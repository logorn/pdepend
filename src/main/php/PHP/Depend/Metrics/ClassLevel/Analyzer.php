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

use \PHP\Depend\AST\Properties;
use \PHP\Depend\AST\Property;

/**
 * Generates some class level based metrics. This analyzer is based on the
 * metrics specified in the following document.
 *
 * http://www.aivosto.com/project/help/pm-oo-misc.html
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
class PHP_Depend_Metrics_ClassLevel_Analyzer
    extends PHP_Depend_Metrics_AbstractAnalyzer
    implements PHP_Depend_Metrics_AggregateAnalyzerI,
               PHP_Depend_Metrics_NodeAware
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
     * Internal status flag used to check if properties are public.
     *
     * @var boolean
     */
    private $public = false;

    /**
     * The internal used cyclomatic complexity analyzer.
     *
     * @var PHP_Depend_Metrics_CyclomaticComplexity_Analyzer
     */
    private $_cyclomaticAnalyzer = null;

    /**
     * This method must return an <b>array</b> of class names for required
     * analyzers.
     *
     * @return array(string)
     */
    public function getRequiredAnalyzers()
    {
        return array(
            PHP_Depend_Metrics_CyclomaticComplexity_Analyzer::CLAZZ
        );
    }

    /**
     * Adds a required sub analyzer.
     *
     * @param PHP_Depend_Metrics_Analyzer $analyzer The sub analyzer instance.
     * @return void
     */
    public function addAnalyzer(PHP_Depend_Metrics_Analyzer $analyzer)
    {
        if ($analyzer instanceof PHP_Depend_Metrics_CyclomaticComplexity_Analyzer) {
            $this->_cyclomaticAnalyzer = $analyzer;
        } else {
            throw new InvalidArgumentException('CC Analyzer required.');
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
     * @param PHP_Depend_AST_Node|string $node The context node instance.
     *
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

    public function visitClassBefore(PHP_Depend_AST_Class $class)
    {
        $impl  = $this->calculateImpl($class);
        $varsi = $this->calculateVARSi($class);
        $wmci  = $this->calculateWMCiForClass($class);

        $metrics = array(
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

        return $metrics;
    }

    public function visitClassAfter(PHP_Depend_AST_Class $class, $data)
    {
        $this->metrics[$class->getId()] = $data;

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
        $this->fireStartTrait($trait);

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

        $this->fireEndTrait($trait);
    }

    /**
     * Visits a method node.
     *
     * @param PHP_Depend_Code_Class $method The method class node.
     *
     * @return void
     */
    public function visitMethod(PHP_Depend_AST_Method $method)
    {
        $this->fireStartMethod($method);

        // Get parent class uuid
        $uuid = $method->getParent()->getUUID();

        $ccn = $this->_cyclomaticAnalyzer->getCCN2($method);

        // Increment Weighted Methods Per Class(WMC) value
        $this->metrics[$uuid][self::M_WEIGHTED_METHODS] += $ccn;
        // Increment Class Size(CSZ) value
        ++$this->metrics[$uuid][self::M_CLASS_SIZE];

        // Increment Non Private values
        if ($method->isPublic()) {
            ++$this->metrics[$uuid][self::M_NUMBER_OF_PUBLIC_METHODS];
            // Increment Non Private WMC value
            $this->metrics[$uuid][self::M_WEIGHTED_METHODS_NON_PRIVATE] += $ccn;
            // Increment Class Interface Size(CIS) value
            ++$this->metrics[$uuid][self::M_CLASS_INTERFACE_SIZE];
        }

        $this->fireEndMethod($method);
    }

    public function visitMethodBefore(PHP_Depend_AST_Method $method, $data)
    {
        $ccn = $this->_cyclomaticAnalyzer->getCCN2($method->getId());

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
     * Visits a property node.
     *
     * @param \PHP\Depend\AST\Property $property The property class node.
     *
     * @return void
     */
    public function visitProperty(Property $property)
    {
        $this->fireStartProperty($property);

        $uuid = $property->getDeclaringClass()->getUUID();

        ++$this->metrics[$uuid][self::M_PROPERTIES];
        ++$this->metrics[$uuid][self::M_CLASS_SIZE];

        if ($property->isPublic()) {

            ++$this->metrics[$uuid][self::M_PROPERTIES_NON_PRIVATE];
            ++$this->metrics[$uuid][self::M_CLASS_INTERFACE_SIZE];
        }

        $this->fireEndProperty($property);
    }

    public function visitPropertiesBefore(Properties $properties, $data)
    {
        $this->public = $properties->isPublic();

        return $data;
    }

    public function visitPropertiesAfter(Properties $properties, $data )
    {
        $this->public = false;

        return $data;
    }

    public function visitPropertyBefore(Property $property, $data)
    {
        ++$data[self::M_PROPERTIES];
        ++$data[self::M_CLASS_SIZE];

        if ($this->public) {

            ++$data[self::M_PROPERTIES_NON_PRIVATE];
            ++$data[self::M_CLASS_INTERFACE_SIZE];
        }

        return $data;
    }

    private function calculateImpl(PHP_Depend_AST_Class $class)
    {
        $implemented = array();
        foreach ($class->getInterfaces() as $interface) {

            $implemented[$interface->getId()] = true;
            foreach ($interface->getInterfaces() as $interface) {

                $implemented[$interface->getId()] = true;
            }
        }

        if ($parentClass = $class->getParentClass()) {

            foreach ($parentClass->getInterfaces() as $interface) {

                $implemented[$interface->getId()] = true;
            }
        }

        return count($implemented);
    }

    /**
     * Calculates the Variables Inheritance of a class metric, this method only
     * counts protected and public properties of parent classes.
     *
     * @param PHP_Depend_AST_Class $class The context class instance.
     *
     * @return integer
     */
    private function calculateVARSi(PHP_Depend_AST_Class $class)
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
     * @param PHP_Depend_AST_Class $class The context class instance.
     *
     * @return integer
     */
    private function calculateWMCiForClass(PHP_Depend_AST_Class $class)
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
                $ccn[$name] = $this->_cyclomaticAnalyzer->getCCN2($method);
            }

            $parentClass = $parentClass->getParentClass();
        }
        return array_sum($ccn);
    }

    /**
     * Calculates the Weight Method Per Class metric for a trait.
     *
     * @param PHP_Depend_AST_Trait $trait The context trait instance.
     *
     * @return integer
     */
    private function calculateWMCiForTrait(PHP_Depend_AST_Trait $trait)
    {
        return array_sum($this->calculateWMCi($trait));
    }

    /**
     * Calculates the Weight Method Per Class metric.
     *
     * @param PHP_Depend_AST_Type $type The context type instance.
     *
     * @return integer[]
     */
    private function calculateWMCi(PHP_Depend_AST_Type $type)
    {
        $ccn = array();

        foreach ($type->getMethods() as $method) {

            $ccn[$method->getName()] = $this->_cyclomaticAnalyzer->getCCN2($method);
        }
        return $ccn;
    }
}
