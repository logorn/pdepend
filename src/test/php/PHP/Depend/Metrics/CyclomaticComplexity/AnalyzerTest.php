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

use \PHP\Depend\Metrics\Processor\DefaultProcessor;

/**
 * Test case for the cyclomatic analyzer.
 *
 * @category   QualityAssurance
 * @package    PHP_Depend
 * @subpackage Metrics
 * @author     Manuel Pichler <mapi@pdepend.org>
 * @copyright  2008-2012 Manuel Pichler. All rights reserved.
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    Release: @package_version@
 * @link       http://pdepend.org/
 *
 * @covers \PHP\Depend\Metrics\AbstractCachingAnalyzer
 * @covers PHP_Depend_Metrics_CyclomaticComplexity_Analyzer
 * @group  pdepend
 * @group  pdepend::metrics
 * @group  pdepend::metrics::cyclomaticcomplexity
 * @group  unittest
 * @group  2.0
 */
class PHP_Depend_Metrics_CyclomaticComplexity_AnalyzerTest
    extends PHP_Depend_Metrics_AbstractTest
{
    /**
     * @var PHP_Depend_Util_Cache_Driver
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

        $this->cache = new PHP_Depend_Util_Cache_Driver_Memory();
    }

    /**
     * Tests that the analyzer calculates the correct function cc numbers.
     *
     * @return void
     */
    public function testCalculateFunctionCCNAndCNN2()
    {
        $processor = new DefaultProcessor();
        $processor->register($analyzer = $this->createAnalyzer());
        $processor->process(self::parseTestCaseSource(__METHOD__));

        $this->assertEquals(
            array(
                'pdepend1' => array('ccn' => 5, 'ccn2' => 6),
                'pdepend2' => array('ccn' => 7, 'ccn2' => 10)
            ),
            array(
                'pdepend1' => $analyzer->getNodeMetrics('pdepend1()#f'),
                'pdepend2' => $analyzer->getNodeMetrics('pdepend2()#f')
            )
        );
    }

    /**
     * testCalculateFunctionCCNAndCNN2ProjectMetrics
     *
     * @return void
     */
    public function testCalculateFunctionCCNAndCNN2ProjectMetrics()
    {
        $processor = new DefaultProcessor();
        $processor->register($analyzer = $this->createAnalyzer());
        $processor->process(self::parseTestCaseSource(__METHOD__));

        $this->assertEquals(
            array('ccn' => 12, 'ccn2' => 16),
            $analyzer->getProjectMetrics()
        );
    }

    /**
     * Tests that the analyzer calculates the correct method cc numbers.
     *
     * @return void
     */
    public function testCalculateMethodCCNAndCNN2()
    {
        $processor = new DefaultProcessor();
        $processor->register($analyzer = $this->createAnalyzer());
        $processor->process(self::parseTestCaseSource(__METHOD__));

        $this->assertEquals(
            array(
                'pdepend1' => array('ccn' => 5, 'ccn2' => 6),
                'pdepend2' => array('ccn' => 7, 'ccn2' => 10)
            ),
            array(
                'pdepend1' => $analyzer->getNodeMetrics('CCMethodClass::pdepend1()#m'),
                'pdepend2' => $analyzer->getNodeMetrics('CCMethodClass::pdepend2()#m')
            )
        );
    }

    /**
     * Tests that the analyzer also detects a conditional expression nested in a
     * compound expression.
     *
     * @return void
     */
    public function testCalculateCCNWithConditionalExprInCompoundExpr()
    {
        $processor = new DefaultProcessor();
        $processor->register($analyzer = $this->createAnalyzer());
        $processor->process(self::parseTestCaseSource(__METHOD__));

        $this->assertEquals(
            array('ccn' => 2, 'ccn2' => 2),
            $analyzer->getProjectMetrics()
        );
    }

    /**
     * testCalculateExpectedCCNForDoWhileStatement
     *
     * @return void
     */
    public function testCalculateExpectedCCNForDoWhileStatement()
    {
        $processor = new DefaultProcessor();
        $processor->register($analyzer = $this->createAnalyzer());
        $processor->process(self::parseTestCaseSource(__METHOD__));

        $this->assertEquals(3, $analyzer->getCCN('func()#f'));
        ;
    }

    /**
     * testCalculateExpectedCCN2ForDoWhileStatement
     *
     * @return void
     */
    public function testCalculateExpectedCCN2ForDoWhileStatement()
    {
        $processor = new DefaultProcessor();
        $processor->register($analyzer = $this->createAnalyzer());
        $processor->process(self::parseTestCaseSource(__METHOD__));

        $this->assertEquals(3, $analyzer->getCCN2('func()#f'));
    }

    /**
     * Tests that the analyzer ignores the default label in a switch statement.
     *
     * @return void
     */
    public function testCalculateCCNIgnoresDefaultLabelInSwitchStatement()
    {
        $processor = new DefaultProcessor();
        $processor->register($analyzer = $this->createAnalyzer());
        $processor->process(self::parseTestCaseSource(__METHOD__));

        $this->assertEquals(
            array('ccn' => 3, 'ccn2' => 3),
            $analyzer->getProjectMetrics()
        );
    }

    /**
     * Tests that the analyzer counts all case labels in a switch statement.
     *
     * @return void
     */
    public function testCalculateCCNCountsAllCaseLabelsInSwitchStatement()
    {
        $processor = new DefaultProcessor();
        $processor->register($analyzer = $this->createAnalyzer());
        $processor->process(self::parseTestCaseSource(__METHOD__));

        $this->assertEquals(
            array('ccn' => 4, 'ccn2' => 4),
            $analyzer->getProjectMetrics()
        );
    }

    /**
     * Tests that the analyzer detects expressions in a for loop.
     *
     * @return void
     */
    public function testCalculateCCNDetectsExpressionsInAForLoop()
    {
        $processor = new DefaultProcessor();
        $processor->register($analyzer = $this->createAnalyzer());
        $processor->process(self::parseTestCaseSource(__METHOD__));

        $this->assertEquals(
            array('ccn' => 2, 'ccn2' => 4),
            $analyzer->getProjectMetrics()
        );
    }

    /**
     * Tests that the analyzer detects expressions in a while loop.
     *
     * @return void
     */
    public function testCalculateCCNDetectsExpressionsInAWhileLoop()
    {
        $processor = new DefaultProcessor();
        $processor->register($analyzer = $this->createAnalyzer());
        $processor->process(self::parseTestCaseSource(__METHOD__));

        $this->assertEquals(
            array('ccn' => 2, 'ccn2' => 4),
            $analyzer->getProjectMetrics()
        );
    }

    /**
     * Tests that the analyzer aggregates the correct project metrics.
     *
     * @return void
     */
    public function testCalculateProjectMetrics()
    {
        $processor = new DefaultProcessor();
        $processor->register($analyzer = $this->createAnalyzer());
        $processor->process(self::parseTestCaseSource(__METHOD__));

        $this->assertEquals(
            array('ccn' => 26, 'ccn2' => 34),
            $analyzer->getProjectMetrics()
        );
    }

    /**
     * testAnalyzerAlsoCalculatesCCNAndCCN2OfClosureInMethod
     *
     * @return void
     */
    public function testAnalyzerAlsoCalculatesCCNAndCCN2OfClosureInMethod()
    {
        $processor = new DefaultProcessor();
        $processor->register($analyzer = $this->createAnalyzer());
        $processor->process(self::parseTestCaseSource(__METHOD__));

        $this->assertEquals(
            array('ccn' => 3, 'ccn2' => 3),
            $analyzer->getProjectMetrics()
        );
    }

    /**
     * testAnalyzerRestoresExpectedFunctionMetricsFromCache
     *
     * @return void
     * @since 1.0.0
     */
    public function testAnalyzerRestoresExpectedFunctionMetricsFromCache()
    {
        $processor = new DefaultProcessor();
        $processor->register($analyzer = $this->createAnalyzer());
        $processor->process(self::parseCodeResourceForTest());

        $metrics0 = $analyzer->getNodeMetrics('testAnalyzerRestoresExpectedMethodMetricsFromCache');

        $processor = new DefaultProcessor();
        $processor->register($analyzer = $this->createAnalyzer());
        $processor->process(self::parseCodeResourceForTest());

        $metrics1 = $analyzer->getNodeMetrics('testAnalyzerRestoresExpectedMethodMetricsFromCache');

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
        $processor = new DefaultProcessor();
        $processor->register($analyzer = $this->createAnalyzer());
        $processor->process(self::parseCodeResourceForTest());

        $metrics0 = $analyzer->getNodeMetrics('baz');

        $processor = new DefaultProcessor();
        $processor->register($analyzer = $this->createAnalyzer());
        $processor->process(self::parseCodeResourceForTest());

        $metrics1 = $analyzer->getNodeMetrics('baz');

        $this->assertEquals($metrics0, $metrics1);
    }

    /**
     * Returns a pre configured ccn analyzer.
     *
     * @return PHP_Depend_Metrics_CyclomaticComplexity_Analyzer
     * @since 1.0.0
     */
    private function createAnalyzer()
    {
        $analyzer = new PHP_Depend_Metrics_CyclomaticComplexity_Analyzer();
        $analyzer->setCache($this->cache);

        return $analyzer;
    }
}
