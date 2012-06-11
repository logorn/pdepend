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

/**
 * This analyzer provides two project related inheritance metrics.
 *
 * <b>ANDC - Average Number of Derived Classes</b>: The average number of direct
 * subclasses of a class. This metric only covers classes in the analyzed system,
 * no library or environment classes are covered.
 *
 * <b>AHH - Average Hierarchy Height</b>: The computed average of all inheritance
 * trees within the analyzed system, external classes or interfaces are ignored.
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
class PHP_Depend_Metrics_Inheritance_Analyzer
    extends PHP_Depend_Metrics_AbstractAnalyzer
    implements PHP_Depend_Metrics_NodeAware,
               PHP_Depend_Metrics_ProjectAware
{
    /**
     * Type of this analyzer class.
     */
    const CLAZZ = __CLASS__;

    /**
     * Metrics provided by the analyzer implementation.
     */
    const M_AVERAGE_NUMBER_DERIVED_CLASSES = 'andc',
        M_AVERAGE_HIERARCHY_HEIGHT         = 'ahh',
        M_DEPTH_OF_INHERITANCE_TREE        = 'dit',
        M_NUMBER_OF_ADDED_METHODS          = 'noam',
        M_NUMBER_OF_OVERWRITTEN_METHODS    = 'noom',
        M_NUMBER_OF_DERIVED_CLASSES        = 'nocc',
        M_MAXIMUM_INHERITANCE_DEPTH        = 'maxDIT',
        M_NUMBER_OF_ROOT_CLASSES           = 'roots',
        M_NUMBER_OF_LEAF_CLASSES           = 'leafs';

    /**
     * Contains the max inheritance depth for all root classes within the
     * analyzed system. The array size is equal to the number of analyzed root
     * classes.
     *
     * @var array
     */
    private $rootClasses = array();

    /**
     * Contains those classes that have at least one class that inherits from
     * that class.
     *
     * @var array
     */
    private $noneLeafClasses = array();

    /**
     * The maximum depth of inheritance tree value within the analyzed source code.
     *
     * @var integer $_maxDIT
     */
    private $maxDIT = 0;

    /**
     * Total number of classes.
     *
     * @var integer
     */
    private $numberOfClasses = 0;

    /**
     * Total number of derived classes.
     *
     * @var integer
     */
    private $numberOfDerivedClasses = 0;

    /**
     * Metrics calculated for a single source node.
     *
     * @var array
     */
    private $metrics = null;

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
     *     'andc'  =>  0.73,
     *     'ahh'   =>  0.56
     * )
     * </code>
     *
     * @return number[]
     */
    public function getProjectMetrics()
    {
        return array(
            self::M_AVERAGE_NUMBER_DERIVED_CLASSES  => $this->getAverageNumberOfDerivedClasses(),
            self::M_AVERAGE_HIERARCHY_HEIGHT        => $this->getAverageHierarchyHeight(),
            self::M_MAXIMUM_INHERITANCE_DEPTH       => $this->maxDIT,
            self::M_NUMBER_OF_LEAF_CLASSES          => $this->numberOfClasses - count($this->noneLeafClasses),
            self::M_NUMBER_OF_ROOT_CLASSES          => count($this->rootClasses),
        );
    }

    /**
     * Returns the average number of derived classes.
     *
     * @return float
     */
    private function getAverageNumberOfDerivedClasses()
    {
        if ($this->numberOfClasses > 0) {
            return $this->numberOfDerivedClasses / $this->numberOfClasses;
        }
        return 0.0;
    }

    /**
     * Returns the average inheritance hierarchy height.
     *
     * @return float
     */
    private function getAverageHierarchyHeight()
    {
        if (($count = count($this->rootClasses)) > 0) {
            return array_sum($this->rootClasses) / $count;
        }
        return 0.0;
    }

    /**
     * Visits a class node.
     *
     * @param PHP_Depend_AST_Class $class The current class node.
     *
     * @return void
     */
    public function visitClassBefore(PHP_Depend_AST_Class $class)
    {
        if (!$class->isUserDefined()) {
            return;
        }

        $this->fireStartClass($class);

        ++$this->numberOfClasses;

        $this->initNodeMetricsForClass($class);

        $this->calculateNumberOfDerivedClasses($class);
        $this->calculateNumberOfAddedAndOverwrittenMethods($class);
        $this->calculateDepthOfInheritanceTree($class);

        $this->fireEndClass($class);
    }

    /**
     * Calculates the number of derived classes.
     *
     * @param PHP_Depend_AST_Class $class The current class node.
     *
     * @return void
     * @since 0.9.5
     */
    private function calculateNumberOfDerivedClasses(PHP_Depend_AST_Class $class)
    {
        $parentClass = $class->getParentClass();
        if (null === $parentClass || false === $parentClass->isUserDefined()) {
            return;
        }

        ++$this->numberOfDerivedClasses;
        ++$this->metrics[$parentClass->getId()][self::M_NUMBER_OF_DERIVED_CLASSES];

        $this->noneLeafClasses[$parentClass->getId()] = true;
    }

    /**
     * Calculates the maximum HIT for the given class.
     *
     * @param PHP_Depend_AST_Class $class The context class instance.
     *
     * @return void
     * @since 0.9.10
     */
    private function calculateDepthOfInheritanceTree(PHP_Depend_AST_Class $class)
    {
        $dep  = 0;
        $dit  = 0;
        $uuid = $class->getId();
        $root = $class->getId();

        $parent = $class;
        while ($parent = $parent->getParentClass()) {
            ++$dit;

            if ($parent->isUserDefined()) {
                ++$dep;
                $root = $parent->getId();
            } else {
                ++$dit;
                break;
            }
        }

        // Collect max dit value
        $this->maxDIT = max($this->maxDIT, $dit);

        if (empty($this->rootClasses[$root]) || $this->rootClasses[$root] < $dep) {
            $this->rootClasses[$root] = $dep;
        }
        $this->metrics[$uuid][self::M_DEPTH_OF_INHERITANCE_TREE] = $dit;
    }

    /**
     * Calculates two metrics. The number of added methods and the number of
     * overwritten methods.
     *
     * @param PHP_Depend_AST_Class $class The context class instance.
     *
     * @return void
     * @since 0.9.10
     */
    private function calculateNumberOfAddedAndOverwrittenMethods(PHP_Depend_AST_Class $class)
    {
        $parentClass = $class->getParentClass();
        if ($parentClass === null) {
            return;
        }

        $parentMethodNames = array();

        $parent = $class;
        while ($parent = $parent->getParentClass()) {
            foreach ($parent->getMethods() as $method) {
                $parentMethodNames[$method->name] = $method->isAbstract();
            }
        }

        $numberOfAddedMethods       = 0;
        $numberOfOverwrittenMethods = 0;

        foreach ($class->getMethods() as $method) {
            if (isset($parentMethodNames[$method->name])) {
                if (!$parentMethodNames[$method->name]) {
                    ++$numberOfOverwrittenMethods;
                }
            } else {
                ++$numberOfAddedMethods;
            }
        }

        $uuid = $class->getId();

        $this->metrics[$uuid][self::M_NUMBER_OF_ADDED_METHODS]       = $numberOfAddedMethods;
        $this->metrics[$uuid][self::M_NUMBER_OF_OVERWRITTEN_METHODS] = $numberOfOverwrittenMethods;
    }

    /**
     * Initializes a empty metric container for the given class node.
     *
     * @param PHP_Depend_AST_Class $class The context class instance.
     *
     * @return void
     * @since 0.9.10
     */
    private function initNodeMetricsForClass(PHP_Depend_AST_Class $class)
    {
        $uuid = $class->getId();
        if (isset($this->metrics[$uuid])) {
            return;
        }

        $this->metrics[$uuid] = array(
            self::M_DEPTH_OF_INHERITANCE_TREE     => 0,
            self::M_NUMBER_OF_ADDED_METHODS       => 0,
            self::M_NUMBER_OF_DERIVED_CLASSES     => 0,
            self::M_NUMBER_OF_OVERWRITTEN_METHODS => 0
        );

        $parent = $class;
        while ($parent = $parent->getParentClass()) {
            $this->initNodeMetricsForClass($parent);
        }
    }
}
