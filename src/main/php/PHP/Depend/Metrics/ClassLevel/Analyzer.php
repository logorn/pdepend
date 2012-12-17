<?php
/**
 * This file is part of PDepend.
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
 * @category  QualityAssurance
 * @author    Manuel Pichler <mapi@pdepend.org>
 * @copyright 2008-2012 Manuel Pichler. All rights reserved.
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version   SVN: $Id$
 * @link      http://pdepend.org/
 */

namespace PHP\Depend\Metrics\ClassLevel;

use \PHP\Depend\AST\ASTType;
use \PHP\Depend\AST\ASTClass;
use \PHP\Depend\AST\ASTMethod;
use \PHP\Depend\AST\ASTProperty;
use \PHP\Depend\Metrics\NodeAware;
use \PHP\Depend\Metrics\AbstractAnalyzer;
use \PHP\Depend\Metrics\AggregateAnalyzer;

/**
 * Generates some class level based metrics. This analyzer is based on the
 * metrics specified in the following document.
 *
 * http://www.aivosto.com/project/help/pm-oo-misc.html
 *
 * @category  QualityAssurance
 * @author    Manuel Pichler <mapi@pdepend.org>
 * @copyright 2008-2012 Manuel Pichler. All rights reserved.
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version   Release: @package_version@
 * @link      http://pdepend.org/
 */
class Analyzer extends AbstractAnalyzer implements AggregateAnalyzer, NodeAware
{
    /**
     * Type of this analyzer class.
     */
    const CLAZZ = __CLASS__;

    /**
     * Metrics provided by the analyzer implementation.
     */
    const M_IMPLEMENTED_INTERFACES     = 'impl',
        M_CLASS_INTERFACE_SIZE         = 'cis',
        M_CLASS_SIZE                   = 'csz',
        M_NUMBER_OF_PUBLIC_METHODS     = 'npm',
        M_PROPERTIES                   = 'vars',
        M_PROPERTIES_INHERIT           = 'varsi',
        M_PROPERTIES_NON_PRIVATE       = 'varsnp',
        M_WEIGHTED_METHODS             = 'wmc',
        M_WEIGHTED_METHODS_INHERIT     = 'wmci',
        M_WEIGHTED_METHODS_NON_PRIVATE = 'wmcnp';

    /**
     * Hash with all calculated node metrics.
     *
     * <code>
     * array(
     *     '0375e305-885a-4e91-8b5c-e25bda005438'  =>  array(
     *         'loc'    =>  42,
     *         'ncloc'  =>  17,
     *         'cc'     =>  12
     *     ),
     *     'e60c22f0-1a63-4c40-893e-ed3b35b84d0b'  =>  array(
     *         'loc'    =>  42,
     *         'ncloc'  =>  17,
     *         'cc'     =>  12
     *     )
     * )
     * </code>
     *
     * @var array(string=>array)
     */
    private $metrics = array();

    /**
     * The internal used cyclomatic complexity analyzer.
     *
     * @var \PHP\Depend\Metrics\CyclomaticComplexity\Analyzer
     */
    private $ccnAnalyzer = null;

    /**
     * This method must return an <b>array</b> of class names for required
     * analyzers.
     *
     * @return array(string)
     */
    public function getRequiredAnalyzers()
    {
        return array(
            \PHP\Depend\Metrics\CyclomaticComplexity\Analyzer::CLAZZ
        );
    }

    /**
     * Adds a required sub analyzer.
     *
     * @param \PHP\Depend\Metrics\Analyzer $analyzer
     * @return void
     */
    public function addAnalyzer(\PHP\Depend\Metrics\Analyzer $analyzer)
    {
        if ($analyzer instanceof \PHP\Depend\Metrics\CyclomaticComplexity\Analyzer) {

            $this->ccnAnalyzer = $analyzer;
        } else {

            throw new \InvalidArgumentException('CC Analyzer required.');
        }
    }

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
     * @return array
     */
    public function getNodeMetrics($node)
    {
        $nodeId = (string) is_object($node) ? $node->getId() : $node;

        if (isset($this->metrics[$nodeId])) {

            return $this->metrics[$nodeId];
        }
        return array();
    }

    /**
     * Initializes the some main metrics for the given class and returns a data
     * structure that is used by the the other visit* methods to store metrics
     * for <b>$class</b>.
     *
     * @param \PHP\Depend\AST\ASTClass $class
     * @return array
     */
    public function visitASTClassBefore(ASTClass $class)
    {
        $impl  = $this->calculateImpl($class);
        $varsi = $this->calculateVARSi($class);
        $wmci  = $this->calculateWMCiForClass($class);

        return array(
            self::M_IMPLEMENTED_INTERFACES       => $impl,
            self::M_CLASS_INTERFACE_SIZE         => 0,
            self::M_CLASS_SIZE                   => 0,
            self::M_NUMBER_OF_PUBLIC_METHODS     => 0,
            self::M_PROPERTIES                   => 0,
            self::M_PROPERTIES_INHERIT           => $varsi,
            self::M_PROPERTIES_NON_PRIVATE       => 0,
            self::M_WEIGHTED_METHODS             => 0,
            self::M_WEIGHTED_METHODS_INHERIT     => $wmci,
            self::M_WEIGHTED_METHODS_NON_PRIVATE => 0
        );
    }

    /**
     * Stores the calculated metrics in <b>$data</b> for the given class and
     * resets the metrics data structure.
     *
     * @param \PHP\Depend\AST\ASTClass $class
     * @param array $data
     * @return null
     */
    public function visitASTClassAfter(ASTClass $class, $data)
    {
        $this->metrics[$class->getId()] = $data;

        return null;
    }

    /**
     * Returns a dummy data structure for the context interface, so that we can
     * handle classes, traits and interface similar, even if we do not measure
     * any interface related metrics.
     *
     * @return array
     */
    public function visitASTInterfaceBefore()
    {
        return array(
            self::M_IMPLEMENTED_INTERFACES       => 0,
            self::M_CLASS_INTERFACE_SIZE         => 0,
            self::M_CLASS_SIZE                   => 0,
            self::M_NUMBER_OF_PUBLIC_METHODS     => 0,
            self::M_PROPERTIES                   => 0,
            self::M_PROPERTIES_INHERIT           => 0,
            self::M_PROPERTIES_NON_PRIVATE       => 0,
            self::M_WEIGHTED_METHODS             => 0,
            self::M_WEIGHTED_METHODS_INHERIT     => 0,
            self::M_WEIGHTED_METHODS_NON_PRIVATE => 0
        );
    }

    /**
     * Just resets all metrics calculated for the context interface.
     *
     * @return null
     */
    public function visitASTInterfaceAfter()
    {
        return null;
    }

    /**
     * Visits a trait node.
     *
     * @param PHP_Depend_AST_Trait $trait The current trait node.
     * @return void
     * @since 1.0.0
     */
    public function visitTrait(PHP_Depend_AST_Trait $trait)
    {
        $wmci = $this->calculateWMCiForTrait($trait);

        $this->metrics[$trait->getUUID()] = array(
            self::M_IMPLEMENTED_INTERFACES       => 0,
            self::M_CLASS_INTERFACE_SIZE         => 0,
            self::M_CLASS_SIZE                   => 0,
            self::M_NUMBER_OF_PUBLIC_METHODS     => 0,
            self::M_PROPERTIES                   => 0,
            self::M_PROPERTIES_INHERIT           => 0,
            self::M_PROPERTIES_NON_PRIVATE       => 0,
            self::M_WEIGHTED_METHODS             => 0,
            self::M_WEIGHTED_METHODS_INHERIT     => $wmci,
            self::M_WEIGHTED_METHODS_NON_PRIVATE => 0
        );

        foreach ($trait->getProperties() as $property) {
            $property->accept($this);
        }
        foreach ($trait->getMethods() as $method) {
            $method->accept($this);
        }
    }

    /**
     * Visits the given property and increments several wmc* and class size
     * related metrics.
     *
     * @param \PHP\Depend\AST\ASTMethod $method
     * @param array $data
     * @return array
     */
    public function visitASTMethodBefore(ASTMethod $method, $data)
    {
        $ccn = $this->ccnAnalyzer->getCCN2($method->getId());

        $data[self::M_WEIGHTED_METHODS] += $ccn;

        ++$data[self::M_CLASS_SIZE];

        if ($method->isPublic()) {

            $data[self::M_WEIGHTED_METHODS_NON_PRIVATE] += $ccn;

            ++$data[self::M_NUMBER_OF_PUBLIC_METHODS];
            ++$data[self::M_CLASS_INTERFACE_SIZE];
        }

        return $data;
    }

    /**
     * Visits the given property and increments several vars* and class size
     * related metrics.
     *
     * @param \PHP\Depend\AST\ASTProperty $property
     * @param array $data
     * @return mixed
     */
    public function visitASTPropertyBefore(ASTProperty $property, $data)
    {
        ++$data[self::M_PROPERTIES];
        ++$data[self::M_CLASS_SIZE];

        if ($property->isPublic()) {

            ++$data[self::M_PROPERTIES_NON_PRIVATE];
            ++$data[self::M_CLASS_INTERFACE_SIZE];
        }

        return $data;
    }

    /**
     * Calculates the total number of interfaces in the whole inheritance
     * hierarchy of this given class.
     *
     * @param \PHP\Depend\AST\ASTClass $class
     * @return integer
     */
    private function calculateImpl(ASTClass $class)
    {
        return count($this->collectImpl($class));
    }

    /**
     * Collects a unique set with all interfaces implemented by the given class,
     * one of it's parents or any implemented interface.
     *
     * @param \PHP\Depend\AST\ASTClass $class
     * @return \PHP\Depend\AST\ASTClass[]
     */
    private function collectImpl(ASTClass $class = null)
    {
        if (null === $class) {
            return array();
        }

        return array_merge(
            $this->collectInterfaces($class->getInterfaces()),
            $this->collectImpl($class->getParentClass())
        );
    }

    /**
     * Collect a unique set of interfaces with the whole interface inheritance
     * hierarchy.
     *
     * @param \PHP\Depend\AST\ASTInterface[] $interfaces
     * @return \PHP\Depend\AST\ASTInterface[]
     */
    private function collectInterfaces(array $interfaces)
    {
        $implemented = array();
        foreach ($interfaces as $interface) {

            $implemented[$interface->getId()] = true;

            $implemented = array_merge(
                $implemented,
                $this->collectInterfaces($interface->getInterfaces())
            );
        }
        return $implemented;
    }

    /**
     * Calculates the Variables Inheritance of a class metric, this method only
     * counts protected and public properties of parent classes.
     *
     * @param \PHP\Depend\AST\ASTClass $class
     * @return integer
     */
    private function calculateVARSi(ASTClass $class)
    {
        $properties = array();
        foreach ($class->getProperties() as $prop) {

            $properties[$prop->getName()] = true;
        }

        $parentClass = $class->getParentClass();
        while ($parentClass) {

            foreach ($parentClass->getProperties() as $property) {

                if ($property->isPrivate()) {
                    continue;
                }

                if (isset($properties[$property->name])) {
                    continue;
                }

                $properties[$property->name] = true;
            }

            $parentClass = $parentClass->getParentClass();
        }

        return count($properties);
    }

    /**
     * Calculates the Weight Method Per Class metric, this method only counts
     * protected and public methods of parent classes.
     *
     * @param \PHP\Depend\AST\ASTClass $class
     * @return integer
     */
    private function calculateWMCiForClass(ASTClass $class)
    {
        $ccn = $this->calculateWMCi($class);

        $parentClass = $class->getParentClass();
        while ($parentClass) {

            foreach ($parentClass->getMethods() as $method) {

                if ($method->isPrivate()) {
                    continue;
                }

                if (isset($ccn[($name = $method->getName())])) {
                    continue;
                }
                $ccn[$name] = $this->ccnAnalyzer->getCCN2($method);
            }

            $parentClass = $parentClass->getParentClass();
        }
        return array_sum($ccn);
    }

    /**
     * Calculates the Weight Method Per Class metric for a trait.
     *
     * @param PHP_Depend_AST_Trait $trait The context trait instance.
     * @return integer
     */
    private function calculateWMCiForTrait(PHP_Depend_AST_Trait $trait)
    {
        return array_sum($this->calculateWMCi($trait));
    }

    /**
     * Calculates the Weight Method Per Class metric.
     *
     * @param \PHP\Depend\AST\ASTType $type
     * @return integer[]
     * @throws \RuntimeException
     */
    private function calculateWMCi(ASTType $type)
    {
        if (null === $this->ccnAnalyzer) {

            throw new \RuntimeException('Missing mandatory CCN analyzer.');
        }

        $ccn = array();

        foreach ($type->getMethods() as $method) {

            $ccn[$method->getName()] = $this->ccnAnalyzer->getCCN2($method);
        }
        return $ccn;
    }
}
