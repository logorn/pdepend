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
 * Calculates the code ranke metric for classes and packages.
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
class PHP_Depend_Metrics_CodeRank_Analyzer
    extends PHP_Depend_Metrics_AbstractAnalyzer
    implements PHP_Depend_Metrics_NodeAware
{
    /**
     * Type of this analyzer class.
     */
    const CLAZZ = __CLASS__;

    /**
     * Metrics provided by the analyzer implementation.
     */
    const M_CODE_RANK       = 'cr',
        M_REVERSE_CODE_RANK = 'rcr';

    /**
     * The used damping factor.
     */
    const DAMPING_FACTOR = 0.85;

    /**
     * Number of loops for the code range calculation.
     */
    const ALGORITHM_LOOPS = 25;

    /**
     * Option key for the code rank mode.
     */
    const STRATEGY_OPTION = 'coderank-mode';

    /**
     * All found nodes.
     *
     * @var array
     */
    private $_nodes = array();

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
     * @var array
     */
    private $metrics = null;

    public function __construct(array $options = array())
    {
        parent::__construct(
            array_merge(
                array(self::STRATEGY_OPTION => array('inheritance')),
                $options
            )
        );
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
        if (null === $this->metrics) {

            $this->buildCodeRankMetrics();
        }

        $nodeId = (string) is_object($node) ? $node->getId() : $node;

        if (isset($this->metrics[$nodeId])) {
            return $this->metrics[$nodeId];
        }
        return array();
    }

    /**
     * Generates the forward and reverse code rank for the given <b>$nodes</b>.
     *
     * @return void
     */
    private function buildCodeRankMetrics()
    {
        foreach (array_keys($this->_nodes) as $uuid) {
            $this->metrics[$uuid] = array(
                self::M_CODE_RANK          => 0,
                self::M_REVERSE_CODE_RANK  => 0
            );
        }
        foreach ($this->computeCodeRank('out', 'in') as $uuid => $rank) {
            $this->metrics[$uuid][self::M_CODE_RANK] = $rank;
        }
        foreach ($this->computeCodeRank('in', 'out') as $uuid => $rank) {
            $this->metrics[$uuid][self::M_REVERSE_CODE_RANK] = $rank;
        }
    }

    /**
     * Calculates the code rank for the given <b>$nodes</b> set.
     *
     * @param string $id1 Identifier for the incoming edges.
     * @param string $id2 Identifier for the outgoing edges.
     *
     * @return array
     */
    private function computeCodeRank($id1, $id2)
    {
        $dampingFactory = self::DAMPING_FACTOR;

        $ranks = array();

        foreach (array_keys($this->_nodes) as $name) {
            $ranks[$name] = 1;
        }

        for ($i = 0; $i < self::ALGORITHM_LOOPS; $i++) {
            foreach ($this->_nodes as $name => $info) {
                $rank = 0;
                foreach ($info[$id1] as $ref) {
                    $previousRank = $ranks[$ref];
                    $refCount     = count($this->_nodes[$ref][$id2]);

                    $rank += ($previousRank / $refCount);
                }
                $ranks[$name] = ((1 - $dampingFactory)) + $dampingFactory * $rank;
            }
        }
        return $ranks;
    }

    public function visitClassBefore(PHP_Depend_AST_Class $class)
    {
        $this->init($class);

        if ($this->isInheritanceDisabled()) {
            return;
        }

        if ($parentClass = $class->getParentClass()) {
            $this->updateType($class, $parentClass);
        }

        foreach ($class->getInterfaces() as $interface) {
            $this->updateType($class, $interface);
        }
    }

    public function visitInterfaceBefore(PHP_Depend_AST_Interface $interface)
    {
        $this->init($interface);

        if ($this->isInheritanceDisabled()) {
            return;
        }

        foreach ($interface->getInterfaces() as $parentInterface) {
            $this->updateType($interface, $parentInterface);
        }
    }

    public function visitPropertyBefore(PHP_Depend_AST_Property $property)
    {
        if ($this->isPropertyDisabled()) {
            return;
        }

        if ($type = $property->getType()) {
            $this->updateType($property->getDeclaringType(), $type);
        }
    }

    public function visitMethodBefore(PHP_Depend_AST_Method $method)
    {
        if ($this->isMethodDisabled()) {
            return array();
        }

        if ($type = $method->getReturnType()) {
            $this->updateType($method->getDeclaringType(), $type);
        }

        foreach ($method->getThrownExceptions() as $thrownException) {
            $this->updateType($method->getDeclaringType(), $thrownException);
        }

        foreach ($method->params as $param) {
            if ($param->typeRef) {
                $this->updateType($method->getDeclaringType(), $param->typeRef);
            }
        }

        return array();
    }

    public function visitMethodAfter(PHP_Depend_AST_Method $method, array $nodes)
    {
        if ($this->isMethodDisabled()) {
            return;
        }

        foreach ($nodes as $node) {
            $this->updateType($method->getDeclaringType(), $node);
        }
    }



    private function updateType(PHP_Depend_AST_Type $in, PHP_Depend_AST_Type $out)
    {
        $this->update($in, $out);
        $this->update($in->getNamespace(), $out->getNamespace());
    }

    private function update(PHP_Depend_AST_Node $in, PHP_Depend_AST_Node $out)
    {
        if ($in->getId() === $out->getId()) {
            return;
        }

        $this->init($in);
        $this->init($out);

        $this->_nodes[$in->getId()]['in'][]   = $out->getId();
        $this->_nodes[$out->getId()]['out'][] = $in->getId();
    }

    private function init(PHP_Depend_AST_Node $node)
    {
        if (isset($this->_nodes[$node->getId()])) {
            return;
        }

        $this->_nodes[$node->getId()] = array(
            'in'   => array(),
            'out'  => array(),
            'name' => $node->getName(),
            'type' => get_class($node)
        );
    }

    private function isInheritanceDisabled()
    {
        return !in_array('inheritance', $this->options[self::STRATEGY_OPTION]);
    }

    private function isMethodDisabled()
    {
        return !in_array('method', $this->options[self::STRATEGY_OPTION]);
    }

    private function isPropertyDisabled()
    {
        return !in_array('property', $this->options[self::STRATEGY_OPTION]);
    }
}
