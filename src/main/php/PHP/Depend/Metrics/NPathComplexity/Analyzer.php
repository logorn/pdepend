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
 * @link      http://www.pdepend.org/
 */

namespace PHP\Depend\Metrics\NPathComplexity;

use \PHP\Depend\AST\ASTClass;
use PHP\Depend\AST\ASTElseIfStatement;
use PHP\Depend\AST\ASTElseStatement;
use \PHP\Depend\AST\ASTFunction;
use \PHP\Depend\AST\ASTInterface;
use \PHP\Depend\AST\ASTMethod;
use \PHP\Depend\Metrics\NodeAware;
use \PHP\Depend\Metrics\AbstractCachingAnalyzer;
use \PHP\Depend\Util\MathUtil;
use PHP\Depend\AST\ASTCallable;
use PHP\Depend\AST\ASTConditionalExpr;
use PHP\Depend\AST\ASTWhileStatement;
use PHP\Depend\AST\ASTBooleanOrExpr;
use PHP\Depend\AST\ASTBooleanAndExpr;
use PHP\Depend\AST\ASTLogicalAndExpr;
use PHP\Depend\AST\ASTLogicalOrExpr;
use PHP\Depend\AST\ASTLogicalXorExpr;
use PHP\Depend\AST\ASTIfStatement;

/**
 * This analyzer calculates the NPath complexity of functions and methods. The
 * NPath complexity metric measures the acyclic execution paths through a method
 * or function. See Nejmeh, Communications of the ACM Feb 1988 pp 188-200.
 *
 * @category  QualityAssurance
 * @author    Manuel Pichler <mapi@pdepend.org>
 * @copyright 2008-2012 Manuel Pichler. All rights reserved.
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version   Release: @package_version@
 * @link      http://www.pdepend.org/
 */
class Analyzer extends AbstractCachingAnalyzer implements NodeAware
{
    /**
     * Type of this analyzer class.
     */
    const CLAZZ = __CLASS__;

    /**
     * Metrics provided by the analyzer implementation.
     */
    const M_NPATH_COMPLEXITY = 'npath';

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
        $nodeId = (string)is_object($node) ? $node->getId() : $node;

        if (isset($this->metrics[$nodeId])) {
            return $this->metrics[$nodeId];
        }
        return array();
    }

    /**
     * Visits a function node.
     *
     * @param \PHP\Depend\AST\ASTFunction $function
     * @return void
     */
    public function visitASTFunctionBefore(ASTFunction $function)
    {
        if (false === $this->restoreFromCache($function)) {

            //$this->calculateComplexity($function);
        }
    }

    /**
     * Visits a method node.
     *
     * @param \PHP\Depend\AST\ASTMethod $method
     * @param array $data
     * @return void
     */
    public function visitASTMethodBefore(ASTMethod $method, array $data = null)
    {
        $this->stack[] = $data;
        return array('1');
    }

    /**
     * Visits a method node.
     *
     * @param \PHP\Depend\AST\ASTMethod $method
     * @param array $data
     * @return void
     */
    public function visitASTMethodAfter(ASTMethod $method, array $data)
    {
        $npath = '1';
        foreach ($data as $childNpath) {
            $npath = MathUtil::mul($npath, $childNpath);
        }

        $this->metrics[$method->getId()] = array(self::M_NPATH_COMPLEXITY => $npath);

        return array_pop($this->stack);
    }

    /**
     * This method will calculate the NPath complexity for the given callable
     * instance.
     *
     * @param \PHP\Depend\AST\ASTCallable $callable
     * @return void
     * @since 0.9.12
     */
    protected function calculateComplexity(ASTCallable $callable)
    {
        $npath = '1';
        foreach ($callable->getChildren() as $child) {
            $stmt  = $child->accept($this, $npath);
            $npath = MathUtil::mul($npath, $stmt);
        }

        $this->metrics[$callable->getId()] = $npath;
    }

    private $stack = array();

    /**
     * This method calculates the NPath Complexity of a conditional-statement,
     * the measured value is then returned as a string.
     *
     * <code>
     * <expr1> ? <expr2> : <expr3>
     *
     * -- NP(?) = NP(<expr1>) + NP(<expr2>) + NP(<expr3>) + 2 --
     * </code>
     *
     * @param \PHP\Depend\AST\ASTConditionalExpr $expr
     * @param array $data
     * @return array
     */
    public function visitASTConditionalExprBefore(ASTConditionalExpr $expr, array $data)
    {
        $this->stack[] = $data;
        return array();
    }

    /**
     * This method calculates the NPath Complexity of a conditional-statement,
     * the measured value is then returned as a string.
     *
     * <code>
     * <expr1> ? <expr2> : <expr3>
     *
     * -- NP(?) = NP(<expr1>) + NP(<expr2>) + NP(<expr3>) + 2 --
     * </code>
     *
     * @param \PHP\Depend\AST\ASTConditionalExpr $expr
     * @param array $data
     * @return array
     */
    public function visitASTConditionalExprAfter(ASTConditionalExpr $expr, array $data)
    {
        $npath = '2';

        foreach (array_pad($data, 3, '1') as $childNpath) {
            if (0 == $childNpath) {
                $childNpath = '1';
            }
            $npath = MathUtil::add($npath, $childNpath);
        }

        $result = array_pop($this->stack);
        $result[] = MathUtil::mul($npath, array_pop($result));

        return $result;
    }

    /**
     * This method calculates the NPath Complexity of a do-while-statement, the
     * measured value is then returned as a string.
     *
     * <code>
     * do
     *   <do-range>
     * while (<expr>)
     * S;
     *
     * -- NP(do) = NP(<do-range>) + NP(<expr>) + 1 --
     * </code>
     *
     * @param PHP_Depend_Code_ASTNodeI $node The currently visited node.
     * @param string                   $data The previously calculated npath value.
     * @return string
     * @since 0.9.12
     */
    public function visitDoWhileStatement($node, $data)
    {
        $stmt = $node->getChild(0)->accept($this, 1);
        $expr = $this->sumComplexity($node->getChild(1));

        $npath = MathUtil::add($expr, $stmt);
        $npath = MathUtil::add($npath, '1');

        return MathUtil::mul($npath, $data);
    }

    /**
     * This method calculates the NPath Complexity of an elseif-statement, the
     * meassured value is then returned as a string.
     *
     * <code>
     * elseif (<expr>)
     *   <elseif-range>
     * S;
     *
     * -- NP(elseif) = NP(<elseif-range>) + NP(<expr>) + 1 --
     *
     *
     * elseif (<expr>)
     *   <elseif-range>
     * else
     *   <else-range>
     * S;
     *
     * -- NP(if) = NP(<if-range>) + NP(<expr>) + NP(<else-range> --
     * </code>
     *
     * @param PHP_Depend_Code_ASTNodeI $node The currently visited node.
     * @param string                   $data The previously calculated npath value.
     * @return string
     * @since 0.9.12
     */
    public function visitElseIfStatement($node, $data)
    {
        $npath = $this->sumComplexity($node->getChild(0));
        foreach ($node->getChildren() as $child) {
            if ($child instanceof PHP_Depend_Code_ASTStatement) {
                $expr  = $child->accept($this, 1);
                $npath = MathUtil::add($npath, $expr);
            }
        }

        if (!$node->hasElse()) {
            $npath = MathUtil::add($npath, '1');
        }

        return MathUtil::mul($npath, $data);
    }

    /**
     * This method calculates the NPath Complexity of a for-statement, the
     * meassured value is then returned as a string.
     *
     * <code>
     * for (<expr1>; <expr2>; <expr3>)
     *   <for-range>
     * S;
     *
     * -- NP(for) = NP(<for-range>) + NP(<expr1>) + NP(<expr2>) + NP(<expr3>) + 1 --
     * </code>
     *
     * @param PHP_Depend_Code_ASTNodeI $node The currently visited node.
     * @param string                   $data The previously calculated npath value.
     * @return string
     * @since 0.9.12
     */
    public function visitForStatement($node, $data)
    {
        $npath = '1';
        foreach ($node->getChildren() as $child) {
            if ($child instanceof PHP_Depend_Code_ASTStatement) {
                $stmt  = $child->accept($this, 1);
                $npath = MathUtil::add($npath, $stmt);
            } else if ($child instanceof PHP_Depend_Code_ASTExpression) {
                $expr  = $this->sumComplexity($child);
                $npath = MathUtil::add($npath, $expr);
            }
        }

        return MathUtil::mul($npath, $data);
    }

    /**
     * This method calculates the NPath Complexity of a for-statement, the
     * meassured value is then returned as a string.
     *
     * <code>
     * fpreach (<expr>)
     *   <foreach-range>
     * S;
     *
     * -- NP(foreach) = NP(<foreach-range>) + NP(<expr>) + 1 --
     * </code>
     *
     * @param PHP_Depend_Code_ASTNodeI $node The currently visited node.
     * @param string                   $data The previously calculated npath value.
     * @return string
     * @since 0.9.12
     */
    public function visitForeachStatement($node, $data)
    {
        $npath = $this->sumComplexity($node->getChild(0));
        $npath = MathUtil::add($npath, '1');

        foreach ($node->getChildren() as $child) {
            if ($child instanceof PHP_Depend_Code_ASTStatement) {
                $stmt  = $child->accept($this, 1);
                $npath = MathUtil::add($npath, $stmt);
            }
        }

        return MathUtil::mul($npath, $data);
    }

    /**
     * This method calculates the NPath Complexity of an if-statement, the
     * meassured value is then returned as a string.
     *
     * <code>
     * if (<expr>)
     *   <if-range>
     * S;
     *
     * -- NP(if) = NP(<if-range>) + NP(<expr>) + 1 --
     *
     *
     * if (<expr>)
     *   <if-range>
     * else
     *   <else-range>
     * S;
     *
     * -- NP(if) = NP(<if-range>) + NP(<expr>) + NP(<else-range> --
     * </code>
     *
     * @param PHP_Depend_Code_ASTNodeI $node The currently visited node.
     * @param string                   $data The previously calculated npath value.
     * @return string
     * @since 0.9.12
     */
    public function visitIfStatement($node, $data)
    {
        $npath = $this->sumComplexity($node->getChild(0));

        foreach ($node->getChildren() as $child) {
            if ($child instanceof PHP_Depend_Code_ASTStatement) {
                $stmt  = $child->accept($this, 1);
                $npath = MathUtil::add($npath, $stmt);
            }
        }

        if (!$node->hasElse()) {
            $npath = MathUtil::add($npath, '1');
        }

        return MathUtil::mul($npath, $data);
    }

    public function visitASTIfStatementBefore(ASTIfStatement $stmt, array $data)
    {
        $this->stack[] = $data;
        return array('1');
    }

    public function visitASTIfStatementAfter(ASTIfStatement $stmt, array $data)
    {
        $npath = '1';

        $result = array_pop($this->stack);
        $result[] = MathUtil::mul($npath, array_pop($result));

        return $result;
    }

    public function visitASTElseIfStatementBefore(ASTElseIfStatement $stmt, array $data)
    {
        return $data;
    }

    public function visitASTElseIfStatementAfter(ASTElseIfStatement $stmt, array $data)
    {
        return $data;
    }

    public function visitASTElseStatementBefore(ASTElseStatement $stmt, array $data)
    {
        return $data;
    }

    public function visitASTElseStatementAfter(ASTElseStatement $stmt, array $data)
    {
        return $data;
    }

    /**
     * This method calculates the NPath Complexity of a return-statement, the
     * measured value is then returned as a string.
     *
     * <code>
     * return <expr>;
     *
     * -- NP(return) = NP(<expr>) --
     * </code>
     *
     * @param PHP_Depend_Code_ASTNodeI $node The currently visited node.
     * @param string                   $data The previously calculated npath value.
     * @return string
     * @since 0.9.12
     */
    public function visitReturnStatement($node, $data)
    {
        if (($npath = $this->sumComplexity($node)) === '0') {
            return $data;
        }
        return MathUtil::mul($npath, $data);
    }

    /**
     * This method calculates the NPath Complexity of a switch-statement, the
     * meassured value is then returned as a string.
     *
     * <code>
     * switch (<expr>)
     *   <case-range1>
     *   <case-range2>
     *   ...
     *   <default-range>
     *
     * -- NP(switch) = NP(<expr>) + NP(<default-range>) +  NP(<case-range1>) ... --
     * </code>
     *
     * @param PHP_Depend_Code_ASTNodeI $node The currently visited node.
     * @param string                   $data The previously calculated npath value.
     * @return string
     * @since 0.9.12
     */
    public function visitSwitchStatement($node, $data)
    {
        $npath = $this->sumComplexity($node->getChild(0));
        foreach ($node->getChildren() as $child) {
            if ($child instanceof PHP_Depend_Code_ASTSwitchLabel) {
                $label = $child->accept($this, 1);
                $npath = MathUtil::add($npath, $label);
            }
        }
        return MathUtil::mul($npath, $data);
    }

    /**
     * This method calculates the NPath Complexity of a try-catch-statement, the
     * meassured value is then returned as a string.
     *
     * <code>
     * try
     *   <try-range>
     * catch
     *   <catch-range>
     *
     * -- NP(try) = NP(<try-range>) + NP(<catch-range>) --
     *
     *
     * try
     *   <try-range>
     * catch
     *   <catch-range1>
     * catch
     *   <catch-range2>
     * ...
     *
     * -- NP(try) = NP(<try-range>) + NP(<catch-range1>) + NP(<catch-range2>) ... --
     * </code>
     *
     * @param PHP_Depend_Code_ASTNodeI $node The currently visited node.
     * @param string                   $data The previously calculated npath value.
     * @return string
     * @since 0.9.12
     */
    public function visitTryStatement($node, $data)
    {
        $npath = '0';
        foreach ($node->getChildren() as $child) {
            if ($child instanceof PHP_Depend_Code_ASTStatement) {
                $stmt  = $child->accept($this, 1);
                $npath = MathUtil::add($npath, $stmt);
            }
        }
        return MathUtil::mul($npath, $data);
    }

    public function visitASTWhileStatementBefore(ASTWhileStatement $node, array $data)
    {
        $this->stack[] = $data;
        return array();
    }

    public function visitASTWhileStatementAfter(ASTWhileStatement $node, array $data)
    {
        $npath = '1';
        foreach (array_slice($data, 1) as $childNpath) {
            $npath = MathUtil::mul($npath, $childNpath);
        }

        $npath = MathUtil::add($npath, $data[1]);
        $npath = MathUtil::add($npath, '1');

        $result = array_pop($this->stack);
        $result[] = $npath;

        return $result;
    }

    public function visitASTBooleanAndExprBefore(ASTBooleanAndExpr $expr, array $data)
    {
        return $this->visitASTExprBefore($data);
    }

    public function visitASTBooleanAndExprAfter(ASTBooleanAndExpr $expr, array $data)
    {
        return $this->visitASTExprAfter($data);
    }

    public function visitASTBooleanOrExprBefore(ASTBooleanOrExpr $expr, array $data)
    {
        return $this->visitASTExprBefore($data);
    }

    public function visitASTBooleanOrExprAfter(ASTBooleanOrExpr $expr, array $data)
    {
        return $this->visitASTExprAfter($data);
    }

    public function visitASTLogicalAndExprBefore(ASTLogicalAndExpr $expr, array $data)
    {
        return $this->visitASTExprBefore($data);
    }

    public function visitASTLogicalAndExprAfter(ASTLogicalAndExpr $expr, array $data)
    {
        return $this->visitASTExprAfter($data);
    }

    public function visitASTLogicalOrExprBefore(ASTLogicalOrExpr $expr, array $data)
    {
        return $this->visitASTExprBefore($data);
    }

    public function visitASTLogicalOrExprAfter(ASTLogicalOrExpr $expr, array $data)
    {
        return $this->visitASTExprAfter($data);
    }

    public function visitASTLogicalXorExprBefore(ASTLogicalXorExpr $expr, array $data)
    {
        return $this->visitASTExprBefore($data);
    }

    public function visitASTLogicalXorExprAfter(ASTLogicalXorExpr $expr, array $data)
    {
        return $this->visitASTExprAfter($data);
    }

    private function visitASTExprBefore(array $data)
    {
        $this->stack[] = $data;
        return array();
    }

    private function visitASTExprAfter(array $data)
    {
        $npath = '1';
        foreach ($data as $childNpath) {
            $npath = MathUtil::add($npath, $childNpath);
        }

        $result = array_pop($this->stack);
        $result[] = $npath;

        return $result;
    }

    /**
     * Calculates the expression sum of the given node.
     *
     * @param PHP_Depend_Code_ASTNodeI $node The currently visited node.
     * @return string
     * @since 0.9.12
     * @todo  I don't like this method implementation, it should be possible to
     *        implement this method with more visitor behavior for the boolean
     *        and logical expressions.
     */
    public function sumComplexity($node)
    {
        $sum = '0';
        if ($node instanceof PHP_Depend_Code_ASTConditionalExpression) {
            $sum = MathUtil::add($sum, $node->accept($this, 1));
        } else if ($node instanceof PHP_Depend_Code_ASTBooleanAndExpression
            || $node instanceof PHP_Depend_Code_ASTBooleanOrExpression
            || $node instanceof PHP_Depend_Code_ASTLogicalAndExpression
            || $node instanceof PHP_Depend_Code_ASTLogicalOrExpression
            || $node instanceof PHP_Depend_Code_ASTLogicalXorExpression
        ) {
            $sum = MathUtil::add($sum, '1');
        } else {
            foreach ($node->getChildren() as $child) {
                $expr = $this->sumComplexity($child);
                $sum  = MathUtil::add($sum, $expr);
            }
        }
        return $sum;
    }

    //==========================================================================

    /**
     *
     *
     * idoskk@param PHP_Depend_Code_ASTNodeI $node The currently visited node.
     * @param string                   $data The previously calculated npath value.
     * @return string
     * @since 0.9.12
     */
    public function visitConditionalExpression($node, $data)
    {
        // New PHP 5.3 ifsetor-operator $x ?: $y
        if (count($node->getChildren()) === 1) {
            $npath = '4';
        } else {
            $npath = '3';
        }

        foreach ($node->getChildren() as $child) {
            if (($cn = $this->sumComplexity($child)) === '0') {
                $cn = '1';
            }
            $npath = MathUtil::add($npath, $cn);
        }

        return MathUtil::mul($npath, $data);
    }

    /**
     * This method calculates the NPath Complexity of a while-statement, the
     * measured value is then returned as a string.
     *
     * <code>
     * while (<expr>)
     *   <while-range>
     * S;
     *
     * -- NP(while) = NP(<while-range>) + NP(<expr>) + 1 --
     * </code>
     *
     * @param PHP_Depend_Code_ASTNodeI $node The currently visited node.
     * @param string                   $data The previously calculated npath value.
     * @return string
     * @since 0.9.12
     */
    public function visitWhileStatement($node, $data)
    {
        $expr = $this->sumComplexity($node->getChild(0));
        $stmt = $node->getChild(1)->accept($this, 1);

        $npath = MathUtil::add($expr, $stmt);
        $npath = MathUtil::add($npath, '1');

        return MathUtil::mul($npath, $data);
    }
}
