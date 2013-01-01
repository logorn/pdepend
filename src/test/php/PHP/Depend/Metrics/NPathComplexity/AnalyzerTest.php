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

namespace PHP\Depend\Metrics\NPathComplexity;

use PHP\Depend\Util\Cache\Driver\Memory;
use PHP\Depend\Metrics\AbstractTest;
use PHP\Depend\AST\ASTCallable;
use PHP\Depend\Metrics\Processor\DefaultProcessor;

/**
 * Test case for the NPath complexity analyzer.
 *
 * @category  QualityAssurance
 * @author    Manuel Pichler <mapi@pdepend.org>
 * @copyright 2008-2012 Manuel Pichler. All rights reserved.
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version   Release: @package_version@
 * @link      http://pdepend.org/
 *
 * @covers     \PHP\Depend\Metrics\AbstractCachingAnalyzer
 * @covers     \PHP\Depend\Metrics\NPathComplexity\Analyzer
 * @group      pdepend
 * @group      pdepend::metrics
 * @group      pdepend::metrics::npathcomplexity
 * @group      unittest
 */
class AnalyzerTest extends AbstractTest
{
    /**
     * @var \PHP\Depend\Util\Cache\CacheDriver
     * @since 1.0.0
     */
    private $cache;

    /**
     * Initializes a in memory cache.
     *
     * @return void
     */
    protected function setUp()
    {
        parent::setUp();

        $this->cache = new Memory();
    }

    /**
     * testAnalyzerRestoresExpectedFunctionMetricsFromCache
     *
     * @return void
     * @since 1.0.0
     */
    public function testAnalyzerRestoresExpectedFunctionMetricsFromCache()
    {
        $this->markTestIncomplete('@todo 2.0');

        $packages = self::parseCodeResourceForTest();
        $function = $packages->current()
            ->getFunctions()
            ->current();

        $analyzer = $this->createAnalyzer();
        $analyzer->analyze($packages);

        $metrics0 = $analyzer->getNodeMetrics($function);

        $analyzer = $this->createAnalyzer();
        $analyzer->analyze($packages);

        $metrics1 = $analyzer->getNodeMetrics($function);

        $this->assertEquals($metrics0, $metrics1);
    }

    /**
     * testAnalyzerRestoresExpectedMethodMetricsFromCache
     *
     * @return void
     * @since 1.0.0
     */
    public function testAnalyzerRestoresExpectedMethodMetricsFromCache()
    {
        $this->markTestIncomplete('@todo 2.0');

        $packages = self::parseCodeResourceForTest();
        $method   = $packages->current()
            ->getClasses()
            ->current()
            ->getMethods()
            ->current();

        $analyzer = $this->createAnalyzer();
        $analyzer->analyze($packages);

        $metrics0 = $analyzer->getNodeMetrics($method);

        $analyzer = $this->createAnalyzer();
        $analyzer->analyze($packages);

        $metrics1 = $analyzer->getNodeMetrics($method);

        $this->assertEquals($metrics0, $metrics1);
    }

    /**
     * testNPathComplexityForNestedIfStatementsWithScope
     *
     * @return void
     * @since 0.9.12
     */
    public function testNPathComplexityForNestedIfStatementsWithScope()
    {
        $this->assertEquals(4, $this->calculateMethodMetric());
    }

    /**
     * testNPathComplexityForNestedIfStatementsWithoutScope
     *
     * @return void
     * @since 0.9.12
     */
    public function testNPathComplexityForNestedIfStatementsWithoutScope()
    {
        $this->assertEquals(4, $this->calculateMethodMetric());
    }

    /**
     * testNPathComplexityForSiblingConditionalExpressions
     *
     * @return void
     * @since 0.9.12
     */
    public function testNPathComplexityForSiblingConditionalExpressions()
    {
        $this->assertEquals(25, $this->calculateFunctionMetric());
    }

    /**
     * testNPathComplexityForSiblingExpressions
     *
     * @return void
     * @since 0.9.12
     * @todo  What happens with boolean/logical expressions within the body of
     *        any other statement/expression?
     */
    public function testNPathComplexityForSiblingExpressions()
    {
        $this->assertEquals(15, $this->calculateFunctionMetric());
    }

    /**
     * testNPathComplexityForTwoSiblingIfStatetements
     *
     * @return void
     * @since 0.9.12
     */
    public function testNPathComplexityForTwoSiblingIfStatetements()
    {
        $this->assertEquals(4, $this->calculateFunctionMetric());
    }

    /**
     * testNPathComplexityForForeachStatementWithNestedIfStatetements
     *
     * @return void
     * @since 0.9.12
     */
    public function testNPathComplexityForForeachStatementWithNestedIfStatetements()
    {
        $this->assertEquals(3, $this->calculateFunctionMetric());
    }

    /**
     * testNPathComplexityForSiblingIfStatementsAndForeachStatement
     *
     * @return void
     * @since 0.9.12
     */
    public function testNPathComplexityForSiblingIfStatementsAndForeachStatement()
    {
        $this->assertEquals(12, $this->calculateFunctionMetric());
    }

    /**
     * testNPathComplexityForComplexFunction
     *
     * @return void
     * @since 0.9.12
     */
    public function testNPathComplexityForComplexFunction()
    {
        $this->assertEquals(60, $this->calculateFunctionMetric());
    }

    /**
     * testNPathComplexityForConditionalsInArrayDeclaration
     *
     * @return void
     * @since 0.9.12
     */
    public function testNPathComplexityForComplexNestedControlStatements()
    {
        $this->assertEquals(63, $this->calculateFunctionMetric());
    }

    /**
     * testNPathComplexityForConditionalsInArrayDeclaration
     *
     * @return void
     * @since 0.9.12
     */
    public function testNPathComplexityForConditionalsInArrayDeclaration()
    {
        $this->assertEquals(625, $this->calculateFunctionMetric());
    }

    /**
     * testNPathComplexityIsZeroForEmptyMethod
     *
     * @return void
     */
    public function testNPathComplexityIsZeroForEmptyMethod()
    {
        $this->assertEquals(1, $this->calculateMethodMetric());
    }

    /**
     * Tests a method body with a simple if statement.
     *
     * @return void
     */
    public function testNPathComplexityForMethodWithSimpleIfStatement()
    {
        $this->assertEquals(2, $this->calculateMethodMetric());
    }

    /**
     * Tests a method body with a simple if statement with dynamic identifier.
     *
     * @return void
     */
    public function testNPathComplexityForIfStatementWithNestedDynamicIdentifier()
    {
        $this->assertEquals(2, $this->calculateMethodMetric());
    }

    /**
     * Tests the analyzer implementation against consecutive if-statements.
     *
     * @return void
     */
    public function testNPathComplexityForConsecutiveIfStatements()
    {
        $this->assertEquals(80, $this->calculateMethodMetric());
    }

    /**
     * Tests the analyzer implementation against multiple if-else-if statements.
     *
     * @return void
     */
    public function testNPathComplexityForConsecutiveIfElseIfStatements()
    {
        $this->assertEquals(4, $this->calculateMethodMetric());
    }

    /**
     * Tests the analyzer implementation against multiple if-elseif statements.
     *
     * @return void
     */
    public function testNPathComplexityForConsecutiveIfElsifStatements()
    {
        $this->assertEquals(4, $this->calculateMethodMetric());
    }

    /**
     * Tests the analyzer implementation against an empty while statement.
     *
     * @return void
     */
    public function testNPathComplexityForEmptyWhileStatement()
    {
        $this->assertEquals(3, $this->calculateMethodMetric());
    }

    /**
     * Tests the anaylzer with nested while statements.
     *
     * @return void
     */
    public function testNPathComplexityForNestedWhileStatements()
    {
        $this->assertEquals(5, $this->calculateMethodMetric());
    }

    /**
     * Tests the npath algorithm with a simple do-while statement.
     *
     * @return void
     */
    public function testNPathComplexityForSimpleDoWhileStatement()
    {
        $this->assertEquals(3, $this->calculateMethodMetric());
    }

    /**
     * Tests the analyzer with a simple for statement.
     *
     * @return void
     */
    public function testNPathComplexityForSimpleForStatement()
    {
        $this->assertEquals(2, $this->calculateMethodMetric());
    }

    /**
     * Tests the analyzer with a complex for statement.
     *
     * @return void
     */
    public function testNPathComplexityForComplexForStatement()
    {
        $this->assertEquals(4, $this->calculateMethodMetric());
    }

    /**
     * Tests the analyzer implementation with a simple foreach statement.
     *
     * @return void
     */
    public function testNPathComplexityForSimpleForeachStatement()
    {
        $this->assertEquals(2, $this->calculateMethodMetric());
    }

    /**
     * Tests the algorithm with a simple return statement.
     *
     * @return void
     */
    public function testNPathComplexityForSimpleReturnStatement()
    {
        $this->assertEquals(1, $this->calculateMethodMetric());
    }

    /**
     * Tests the algorithm with a return statement that contains boolean expressions.
     *
     * @return void
     */
    public function testNPathComplexityForReturnStatementWithBooleanExpressions()
    {
        $this->assertEquals(2, $this->calculateMethodMetric());
    }

    /**
     * Tests the algorithm with a return statement that contains a conditional.
     *
     * @return void
     */
    public function testNPathComplexityForReturnStatementWithConditionalStatement()
    {
        $this->assertEquals(5, $this->calculateMethodMetric());
    }

    /**
     * Tests the algorithm with a simple switch statement that contains one case
     * child.
     *
     * @return void
     */
    public function testNPathComplexityForSimpleSwitchStatement()
    {
        $this->assertEquals(1, $this->calculateMethodMetric());
    }

    /**
     * Tests the algorithm with a switch statement that contains multiple case
     * statements.
     *
     * @return void
     */
    public function testNPathComplexityForSwitchStatementWithMultipleCaseStatements()
    {
        $this->assertEquals(5, $this->calculateMethodMetric());
    }

    /**
     * Tests the algorithm with a switch statement that contains complex case
     * statements.
     *
     * @return void
     */
    public function testNPathComplexityForSwitchStatementWithComplexCaseStatements()
    {
        $this->assertEquals(8, $this->calculateMethodMetric());
    }

    /**
     * Tests the algorithm with a simple try statement.
     *
     * @return void
     */
    public function testNPathComplexityForSimpleTryCatchStatement()
    {
        $this->assertEquals(2, $this->calculateMethodMetric());
    }

    /**
     * Tests the algorithm with a try statement with multiple catch statements.
     *
     * @return void
     */
    public function testNPathComplexityForTryStatementWithMutlipleCatchStatements()
    {
        $this->assertEquals(5, $this->calculateMethodMetric());
    }

    /**
     * Tests the algorithm with a try statement with nested if statements.
     *
     * @return void
     */
    public function testNPathComplexityForTryCatchStatementWithNestedIfStatements()
    {
        $this->assertEquals(5, $this->calculateMethodMetric());
    }

    /**
     * Tests the algorithm with a conditional statement.
     *
     * @return void
     */
    public function testNPathComplexityForSimpleConditionalStatement()
    {
        $this->assertEquals(5, $this->calculateMethodMetric());
    }

    /**
     * Tests the algorithm with nested conditional statements.
     *
     * @return void
     */
    public function testNPathComplexityForTwoNestedConditionalStatements()
    {
        $this->assertEquals(9, $this->calculateMethodMetric());
    }

    /**
     * Tests the algorithm with nested conditional statements.
     *
     * @return void
     */
    public function testNPathComplexityForThreeNestedConditionalStatements()
    {
        $this->assertEquals(13, $this->calculateMethodMetric());
    }

    /**
     * Tests the algorithm with a conditional statement with boolean/logical
     * expressions.
     *
     * @return void
     */
    public function testNPathComplexityForConditionalStatementWithLogicalExpressions()
    {
        $this->assertEquals(6, $this->calculateMethodMetric());
    }

    /**
     * Tests the algorithm implementation against an existing bug in the
     * combination of return, conditional and if statement.
     *
     * @return void
     */
    public function testNPathComplexityForReturnStatementWithConditional()
    {
        $this->assertEquals(6, $this->calculateMethodMetric());
    }

    /**
     * Returns the NPath Complexity of the first function found in source file
     * associated with the calling test case.
     *
     * @return integer
     * @since 0.9.12
     */
    private function calculateFunctionMetric()
    {
        $this->markTestIncomplete('@todo 2.0');

        return $this->calculateNPathComplexity(
            $this->_getFirstFunctionForTestCase()
        );
    }

    /**
     * Parses the source code associated with the calling test case and returns
     * the first function found in the test case source file.
     *
     * @return PHP_Depend_Code_Function
     * @since 0.9.12
     */
    private function _getFirstFunctionForTestCase()
    {
        return self::parseTestCaseSource(self::getCallingTestMethod())
            ->current()
            ->getFunctions()
            ->current();
    }

    /**
     * Returns the NPath Complexity of the first method found in source file
     * associated with the calling test case.
     *
     * @return integer
     * @since 0.9.12
     */
    private function calculateMethodMetric()
    {
        list(, $method) = explode('::', $this->getCallingTestMethod());

        $processor = new DefaultProcessor();
        $processor->register($analyzer = new \PHP\Depend\Metrics\NPathComplexity\Analyzer());
        $processor->process($this->parseCodeResourceForTest());

        $metrics = $analyzer->getNodeMetrics("{$method}::{$method}()#m");
        return $metrics['npath'];
    }

    /**
     * Calculates the NPath complexity for the given callable instance.
     *
     * @param \PHP\Depend\AST\ASTCallable $callable
     * @return string
     * @since 0.9.12
     */
    private function calculateNPathComplexity(ASTCallable $callable)
    {
        $analyzer = $this->createAnalyzer();
        $callable->accept($analyzer);

        $metrics = $analyzer->getNodeMetrics($callable);
        return $metrics['npath'];
    }

    /**
     * Creates a ready to use npath complexity analyzer.
     *
     * @return \PHP\Depend\Metrics\NPathComplexity\Analyzer
     * @since 1.0.0
     */
    private function createAnalyzer()
    {
        $analyzer = new Analyzer();
        $analyzer->setCache($this->cache);

        return $analyzer;
    }
}
