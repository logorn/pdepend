<?php
/**
 * This file is part of PHP_Depend.
 *
 * PHP Version 5
 *
 * Copyright (c) 2008-2012, Manuel Pichler <mapi@pdepend.org>.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *   * Redistributions of source code must retain the above copyright
 *     notice, this list of conditions and the following disclaimer.
 *
 *   * Redistributions in binary form must reproduce the above copyright
 *     notice, this list of conditions and the following disclaimer in
 *     the documentation and/or other materials provided with the
 *     distribution.
 *
 *   * Neither the name of Manuel Pichler nor the names of his
 *     contributors may be used to endorse or promote products derived
 *     from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
 * FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 * COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
 * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @category   QualityAssurance
 * @package    PHP_Depend
 * @subpackage Log
 * @author     Manuel Pichler <mapi@pdepend.org>
 * @copyright  2008-2012 Manuel Pichler. All rights reserved.
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    SVN: $Id$
 * @link       http://pdepend.org/
 */

use \PHP\Depend\AST\ASTNode;
use \PHP\Depend\AST\ASTClass;
use \PHP\Depend\AST\ASTFunction;
use \PHP\Depend\AST\ASTInterface;
use \PHP\Depend\AST\ASTMethod;
use \PHP\Depend\AST\ASTNamespace;
use \PHP\Depend\AST\ASTCompilationUnit;
use \PHP\Depend\Log\CodeAware;
use \PHP\Depend\Log\FileAware;

/**
 * This logger generates a summary xml document with aggregated project, class,
 * method and file metrics.
 *
 * @category   QualityAssurance
 * @package    PHP_Depend
 * @subpackage Log
 * @author     Manuel Pichler <mapi@pdepend.org>
 * @copyright  2008-2012 Manuel Pichler. All rights reserved.
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    Release: @package_version@
 * @link       http://pdepend.org/
 */
class PHP_Depend_Log_Summary_Xml implements CodeAware, FileAware
{
    /**
     * The type of this class.
     */
    const CLAZZ = __CLASS__;

    /**
     * The log output file.
     *
     * @var string
     */
    private $_logFile = null;

    /**
     * List of all analyzers that implement the node aware interface.
     *
     * @var PHP_Depend_Metrics_Analyzer[]
     */
    private $_nodeAwareAnalyzers = array();

    /**
     * List of all analyzers that implement the project aware interface.
     *
     * @var PHP_Depend_Metrics_ProjectAware[]
     */
    private $_projectAwareAnalyzers = array();

    /**
     * @var DOMDocument
     */
    private $document;

    /**
     * @var DOMElement[]
     */
    private $elements = array();

    public function __construct()
    {
        $this->document = new DOMDocument('1.0', 'UTF-8');

        $this->document->formatOutput = true;

        $metrics = $this->document->createElement('metrics');
        $metrics->setAttribute('generated', date('Y-m-d\TH:i:s'));
        $metrics->setAttribute('pdepend', '@package_version@');
        $metrics->appendChild($this->document->createElement('files'));

        $this->document->appendChild($metrics);
    }

    /**
     * Sets the output log file.
     *
     * @param string $logFile The output log file.
     *
     * @return void
     */
    public function setLogFile($logFile)
    {
        $this->_logFile = $logFile;
    }

    /**
     * Returns an <b>array</b> with accepted analyzer types. These types can be
     * concrete analyzer classes or one of the descriptive analyzer interfaces.
     *
     * @return string[]
     */
    public function getAcceptedAnalyzers()
    {
        return array(
            'PHP_Depend_Metrics_NodeAware',
            'PHP_Depend_Metrics_ProjectAware'
        );
    }

    /**
     * Adds an analyzer to log. If this logger accepts the given analyzer it
     * with return <b>true</b>, otherwise the return value is <b>false</b>.
     *
     * @param PHP_Depend_Metrics_Analyzer $analyzer
     *
     * @return boolean
     */
    public function log(PHP_Depend_Metrics_Analyzer $analyzer)
    {
        $accepted = false;
        if ($analyzer instanceof PHP_Depend_Metrics_ProjectAware) {
            $this->_projectAwareAnalyzers[] = $analyzer;

            $accepted = true;
        }
        if ($analyzer instanceof PHP_Depend_Metrics_NodeAware) {
            $this->_nodeAwareAnalyzers[] = $analyzer;

            $accepted = true;
        }
        return $accepted;
    }

    /**
     * Closes the logger process and writes the output file.
     *
     * @return void
     * @throws PHP_Depend_Log_NoLogOutputException If the no log target exists.
     */
    public function close()
    {
        if ($this->_logFile === null) {
            throw new PHP_Depend_Log_NoLogOutputException($this);
        }

        foreach ($this->_getProjectMetrics() as $name => $value) {
            $this->document->documentElement->setAttribute($name, $value);
        }

        $this->document->save($this->_logFile);
    }

    public function visitASTCompilationUnitBefore(ASTCompilationUnit $compilationUnit)
    {
        $element = $this->document->createElement('file');
        $element->setAttribute('name', $compilationUnit->file);

        $this->writeMetrics($compilationUnit, $element);

        $this->document->documentElement->firstChild->appendChild($element);

        $this->elements[] = $element;

        return $element;
    }

    public function visitASTCompilationUnitAfter(ASTCompilationUnit $compilationUnit)
    {
        array_pop($this->elements);
    }

    public function visitASTNamespaceBefore(ASTNamespace $namespace, DOMElement $file)
    {
        $xpath  = new DOMXPath($this->document);
        $result = $xpath->query("//package[@name='{$namespace->name}']");

        if (0 === $result->length) {
            $element = $this->document->createElement('package');
            $element->setAttribute('name', $namespace->name);

            $this->writeMetrics($namespace, $element);

            $this->document->documentElement->appendChild($element);
        } else {
            $element = $result->item(0);
        }

        $this->elements[] = $file;

        return $element;
    }

    public function visitASTNamespaceAfter(ASTNamespace $ns, DOMElement $xml)
    {
        if (0 === $xml->childNodes->length) {
            $this->document->documentElement->removeChild($xml);
        }

        return array_pop($this->elements);
    }

    public function visitASTClassBefore(ASTClass $class, DOMElement $namespace)
    {
        $element = $this->document->createElement('class');
        $element->setAttribute('name', $class->name);

        $this->writeMetrics($class, $element);

        $file = $this->document->createElement('file');
        $file->setAttribute('name', $this->elements[0]->getAttribute('name'));

        $element->appendChild($file);

        $namespace->appendChild($element);

        $this->elements[] = $namespace;

        return $element;
    }

    public function visitASTClassAfter()
    {
        return array_pop($this->elements);
    }

    public function visitASTInterfaceBefore(ASTInterface $interface, DOMElement $namespace)
    {
        $this->elements[] = $namespace;

        return $this->document->createElement('interface');
    }

    public function visitASTInterfaceAfter()
    {
        return array_pop($this->elements);
    }

    public function visitASTMethodBefore(ASTMethod $method, DOMElement $type)
    {
        $element = $this->document->createElement('method');
        $element->setAttribute('name', $method->name);

        $this->writeMetrics($method, $element);

        $type->appendChild($element);

        $this->elements[] = $type;

        return $element;
    }

    public function visitASTMethodAfter()
    {
        return array_pop($this->elements);
    }

    public function visitASTFunctionBefore(ASTFunction $function, DOMElement $namespace)
    {
        $element = $this->document->createElement('function');
        $element->setAttribute('name', $function->name);

        $this->writeMetrics($function, $element);

        $file = $this->document->createElement('file');
        $file->setAttribute('name', $this->elements[0]->getAttribute('name'));

        $element->appendChild($file);

        $namespace->appendChild($element);

        $this->elements[] = $namespace;

        return $namespace;
    }

    public function visitASTFunctionAfter()
    {
        return array_pop($this->elements);
    }

    private function writeMetrics(ASTNode $node, DOMElement $element)
    {
        foreach ($this->getNodeMetrics($node) as $name => $value) {
            $element->setAttribute($name, $value);
        }
    }

    private function getNodeMetrics(ASTNode $node)
    {
        $metrics = array();
        foreach ($this->_nodeAwareAnalyzers as $analyzer) {
            $metrics = array_merge($metrics, $analyzer->getNodeMetrics($node));
        }
        ksort($metrics);
        return $metrics;
    }

    /**
     * Returns an array with all collected project metrics.
     *
     * @return array(string=>mixed)
     * @since 0.9.10
     */
    private function _getProjectMetrics()
    {
        $projectMetrics = array();
        foreach ($this->_projectAwareAnalyzers as $analyzer) {
            $projectMetrics = array_merge(
                $projectMetrics,
                $analyzer->getProjectMetrics()
            );
        }
        ksort($projectMetrics);

        return $projectMetrics;
    }
}
