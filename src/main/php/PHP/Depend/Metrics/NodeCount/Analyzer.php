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
 * @subpackage Metrics
 * @author     Manuel Pichler <mapi@pdepend.org>
 * @copyright  2008-2012 Manuel Pichler. All rights reserved.
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    SVN: $Id$
 * @link       http://pdepend.org/
 */

use \PHP\Depend\AST\ASTClass;
use \PHP\Depend\AST\ASTFunction;
use \PHP\Depend\AST\ASTInterface;
use \PHP\Depend\AST\ASTMethod;
use \PHP\Depend\AST\ASTNamespace;

/**
 * This analyzer collects different count metrics for code artifacts like
 * classes, methods, functions or packages.
 *
 * @category   QualityAssurance
 * @package    PHP_Depend
 * @subpackage Metrics
 * @author     Manuel Pichler <mapi@pdepend.org>
 * @copyright  2008-2012 Manuel Pichler. All rights reserved.
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    Release: @package_version@
 * @link       http://pdepend.org/
 */
class PHP_Depend_Metrics_NodeCount_Analyzer extends PHP_Depend_Metrics_AbstractAnalyzer implements PHP_Depend_Metrics_NodeAware, PHP_Depend_Metrics_ProjectAware
{
    /**
     * Type of this analyzer class.
     */
    const CLAZZ = __CLASS__;

    /**
     * Metrics provided by the analyzer implementation.
     */
    const M_NUMBER_OF_PACKAGES       = 'nop',
        M_NUMBER_OF_CLASSES          = 'noc',
        M_NUMBER_OF_INTERFACES       = 'noi',
        M_NUMBER_OF_METHODS          = 'nom',
        M_NUMBER_OF_FUNCTIONS        = 'nof',
        M_NUMBER_OF_ABSTRACT_CLASSES = 'clsa',
        M_NUMBER_OF_CONCRETE_CLASSES = 'clsc';

    /**
     * Number Of Packages.
     *
     * @var integer
     */
    private $numberOfPackages = 0;

    /**
     * Number Of Classes.
     *
     * @var integer
     */
    private $numberOfClasses = 0;

    /**
     * Number of abstract classes.
     *
     * @var integer
     */
    private $numberOfAbstractClasses = 0;

    /**
     * Number Of Interfaces.
     *
     * @var integer
     */
    private $numberOfInterfaces = 0;

    /**
     * Number Of Methods.
     *
     * @var integer
     */
    private $numberOfMethods = 0;

    /**
     * Number Of Functions.
     *
     * @var integer
     */
    private $numberOfFunctions = 0;

    /**
     * Collected node metrics
     *
     * @var array
     */
    private $metrics = array();

    /**
     * This method will return an <b>array</b> with all generated metric values
     * for the given node or node identifier. If there are no metrics for the
     * requested node, this method will return an empty <b>array</b>.
     *
     * <code>
     * array(
     *     'noc'  =>  23,
     *     'nom'  =>  17,
     *     'nof'  =>  42
     * )
     * </code>
     *
     * @param \PHP\Depend\AST\ASTNode|string $node
     *
     * @return array
     */
    public function getNodeMetrics($node)
    {
        $nodeId = (string)is_object($node) ? $node->getId() : $node;

        if (isset($this->metrics[$nodeId])) {
            return $this->metrics[$nodeId];
        }
        return array();
    }

    /**
     * Provides the project summary as an <b>array</b>.
     *
     * <code>
     * array(
     *     'nop'  =>  23,
     *     'noc'  =>  17,
     *     'noi'  =>  23,
     *     'nom'  =>  42,
     *     'nof'  =>  17
     * )
     * </code>
     *
     * @return array(string=>mixed)
     */
    public function getProjectMetrics()
    {
        return array(
            self::M_NUMBER_OF_PACKAGES         => $this->numberOfPackages,
            self::M_NUMBER_OF_CLASSES          => $this->numberOfClasses,
            self::M_NUMBER_OF_INTERFACES       => $this->numberOfInterfaces,
            self::M_NUMBER_OF_METHODS          => $this->numberOfMethods,
            self::M_NUMBER_OF_FUNCTIONS        => $this->numberOfFunctions,
            self::M_NUMBER_OF_ABSTRACT_CLASSES => $this->numberOfAbstractClasses,
            self::M_NUMBER_OF_CONCRETE_CLASSES => ($this->numberOfClasses - $this->numberOfAbstractClasses)
        );
    }

    /**
     * Visits the given class before it's children were traversed.
     *
     * @param \PHP\Depend\AST\ASTClass $class
     * @param mixed $data
     *
     * @return mixed
     */
    public function visitASTClassBefore(ASTClass $class, $data = null)
    {
        ++$this->numberOfClasses;

        if ($class->isAbstract()) {
            ++$this->numberOfAbstractClasses;
        }

        $this->metrics[$class->getId()] = array(self::M_NUMBER_OF_METHODS => 0);

        $this->updateNamespace($class->getNamespace(), self::M_NUMBER_OF_CLASSES);

        return $data;
    }

    /**
     * Visits the given interface before it's children were traversed.
     *
     * @param \PHP\Depend\AST\ASTInterface $interface
     * @param mixed $data
     *
     * @return mixed
     */
    public function visitASTInterfaceBefore(ASTInterface $interface, $data)
    {
        ++$this->numberOfInterfaces;

        $this->metrics[$interface->getId()] = array(self::M_NUMBER_OF_METHODS => 0);

        $this->updateNamespace($interface->getNamespace(), self::M_NUMBER_OF_INTERFACES);

        return $data;
    }

    /**
     * Visits the given method before it's children were traversed.
     *
     * @param \PHP\Depend\AST\ASTMethod $method
     * @param mixed $data
     *
     * @return mixed
     * @todo Do not count methods declared in an interface
     */
    public function visitASTMethodBefore(ASTMethod $method, $data)
    {
        ++$this->numberOfMethods;

        $this->updateType($method->getDeclaringType(), self::M_NUMBER_OF_METHODS);
        $this->updateNamespace($method->getNamespace(), self::M_NUMBER_OF_METHODS);

        return $data;
    }

    /**
     * Visits the given function before it's children were traversed.
     *
     * @param \PHP\Depend\AST\ASTFunction $function
     * @param mixed $data
     *
     * @return mixed
     */
    public function visitASTFunctionBefore(ASTFunction $function, $data)
    {
        ++$this->numberOfFunctions;

        $this->updateNamespace($function->getNamespace(), self::M_NUMBER_OF_FUNCTIONS);

        return $data;
    }

    /**
     * Visits the given namespace before it's children were traversed.
     *
     * @param \PHP\Depend\AST\ASTNamespace $ns
     * @param mixed $data
     *
     * @return mixed
     */
    public function visitASTNamespaceBefore(ASTNamespace $ns, $data)
    {
        if (false === isset($this->metrics[$ns->getId()])) {
            $this->metrics[$ns->getId()] = array(
                self::M_NUMBER_OF_CLASSES     => 0,
                self::M_NUMBER_OF_INTERFACES  => 0,
                self::M_NUMBER_OF_METHODS     => 0,
                self::M_NUMBER_OF_FUNCTIONS   => 0
            );

            ++$this->numberOfPackages;
        }
        return $data;
    }

    /**
     * Increments the metric identified by <b>$metricId</b> on the given
     * <b>$namespace</b> object.
     *
     * @param \PHP\Depend\AST\ASTNamespace $namespace
     * @param string $metricId
     *
     * @return void
     */
    private function updateNamespace(ASTNamespace $namespace, $metricId)
    {
        $this->visitASTNamespaceBefore($namespace, null);

        ++$this->metrics[$namespace->getId()][$metricId];
    }

    /**
     * Increments the metric identified by <b>$metricId</b> on the given
     * <b>$type</b> object.
     *
     * @param PHP_Depend_AST_Type $type
     * @param string              $metricId
     *
     * @return void
     */
    private function updateType(PHP_Depend_AST_Type $type, $metricId)
    {
        ++$this->metrics[$type->getId()][$metricId];
    }
}