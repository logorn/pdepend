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
 * @subpackage Metrics_CrapIndex
 * @author     Manuel Pichler <mapi@pdepend.org>
 * @copyright  2008-2012 Manuel Pichler. All rights reserved.
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    SVN: $Id$
 * @link       http://pdepend.org/
 */

use \PHP\Depend\Metrics\Processor\DefaultProcessor;

/**
 * Test cases for the {@link PHP_Depend_Metrics_CrapIndex_Analyzer} class.
 *
 * @category   QualityAssurance
 * @package    PHP_Depend
 * @subpackage Metrics_CrapIndex
 * @author     Manuel Pichler <mapi@pdepend.org>
 * @copyright  2008-2012 Manuel Pichler. All rights reserved.
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    Release: @package_version@
 * @link       http://pdepend.org/
 *
 * @covers PHP_Depend_Metrics_CrapIndex_Analyzer
 * @group  pdepend
 * @group  pdepend::metrics
 * @group  pdepend::metrics::crapindex
 * @group  unittest
 */
class PHP_Depend_Metrics_CrapIndex_AnalyzerTest extends PHP_Depend_Metrics_AbstractTest
{
    /**
     * testReturnsExpectedDependencies
     *
     * @return void
     */
    public function testReturnsExpectedDependencies()
    {
        $analyzer = new PHP_Depend_Metrics_CrapIndex_Analyzer();
        $actual   = $analyzer->getRequiredAnalyzers();
        $expected = array(PHP_Depend_Metrics_CyclomaticComplexity_Analyzer::CLAZZ);

        $this->assertEquals($expected, $actual);
    }

    /**
     * testCrapIndexIsDisabledWhenReportNotSupplied
     *
     * @return void
     */
    public function testCrapIndexIsDisabledWhenReportNotSupplied()
    {
        $analyzer = new PHP_Depend_Metrics_CrapIndex_Analyzer();

        $this->assertFalse($analyzer->isEnabled());
    }

    /**
     * testCrapIndexIsEnabledWhenReportSupplied
     *
     * @return void
     */
    public function testCrapIndexIsEnabledWhenReportSupplied()
    {
        $options  = array('coverage-report' => $this->createCloverReportFile());
        $analyzer = new PHP_Depend_Metrics_CrapIndex_Analyzer($options);

        $this->assertTrue($analyzer->isEnabled());
    }

    /**
     * testCrapIndexIgnoresAbstractMethods
     *
     * @return void
     */
    public function testCrapIndexIgnoresAbstractMethods()
    {
        $metrics = $this->calculateCrapIndex(__FUNCTION__ . 'foo()#m', 42);
        $this->assertSame(array(), $metrics);
    }

    /**
     * testCrapIndexIgnoresInterfaceMethods
     *
     * @return void
     */
    public function testCrapIndexIgnoresInterfaceMethods()
    {
        $metrics = $this->calculateCrapIndex(__FUNCTION__ . 'foo()#m', 42);
        $this->assertSame(array(), $metrics);
    }

    /**
     * testAnalyzerReturnsExpectedResultForMethodWithoutCoverage
     *
     * @return void
     */
    public function testCrapIndexForMethodWithoutCoverage()
    {
        $this->doTestCrapIndexCalculation(__FUNCTION__ . '::foo()#m', 12, 156);
    }

    /**
     * testCrapIndexForMethodWith100PercentCoverage
     *
     * @return void
     */
    public function testCrapIndexForMethodWith100PercentCoverage()
    {
        $this->doTestCrapIndexCalculation(__FUNCTION__ . '::foo()#m', 12, 12);
    }

    /**
     * testCrapIndexForMethodWith50PercentCoverage
     *
     * @return void
     */
    public function testCrapIndexForMethodWith50PercentCoverage()
    {
        $this->doTestCrapIndexCalculation(__FUNCTION__ . '::foo()#m', 12, 30);
    }

    /**
     * testCrapIndexForMethodWithoutCoverageData
     *
     * @return void
     */
    public function testCrapIndexForMethodWithoutCoverageData()
    {
        $this->doTestCrapIndexCalculation(__FUNCTION__ . '::foo()#m', 12, 156);
    }

    /**
     * testCrapIndexForFunctionWithoutCoverageData
     *
     * @return void
     */
    public function testCrapIndexForFunctionWithoutCoverageData()
    {
        $this->doTestCrapIndexCalculation(__FUNCTION__ . '()#f', 12, 156);
    }

    /**
     * Tests the crap index algorithm implementation.
     *
     * @param string  $nodeId
     * @param integer $ccn
     * @param integer $crapIndex
     *
     * @return void
     */
    private function doTestCrapIndexCalculation($nodeId, $ccn, $crapIndex)
    {
        $metrics = $this->calculateCrapIndex($nodeId, $ccn);
        $this->assertEquals($crapIndex, $metrics['crap'], '', 0.005);
    }

    /**
     * Calculates the crap index.
     *
     * @param string  $nodeId
     * @param integer $ccn
     *
     * @return array
     */
    private function calculateCrapIndex($nodeId, $ccn)
    {
        $options  = array('coverage-report' => $this->createCloverReportFile());
        $analyzer = new PHP_Depend_Metrics_CrapIndex_Analyzer($options);
        $analyzer->addAnalyzer($this->createCyclomaticComplexityAnalyzerMock($ccn));

        $processor = new DefaultProcessor();
        $processor->register($analyzer);
        $processor->process(self::parseCodeResourceForTest());

        return $analyzer->getNodeMetrics($nodeId);
    }

    /**
     * Creates a temporary clover report file that can be used for a single test.
     *
     * @return string
     */
    private function createCloverReportFile()
    {
        $pathName = self::createRunResourceURI('clover.xml');

        $content = file_get_contents(__DIR__ . '/_files/clover.xml');
        $content = str_replace('${pathName}', dirname(self::createCodeResourceUriForTest()), $content);
        file_put_contents($pathName, $content);

        return $pathName;
    }

    /**
     * Creates a mocked instance of the cyclomatic complexity analyzer.
     *
     * @param integer $ccn The expected ccn result value.
     *
     * @return PHP_Depend_Metrics_CyclomaticComplexity_Analyzer
     */
    private function createCyclomaticComplexityAnalyzerMock($ccn = 42)
    {
        $mock = $this->getMock(PHP_Depend_Metrics_CyclomaticComplexity_Analyzer::CLAZZ);
        $mock->expects($this->any())
            ->method('getCCN2')
            ->will($this->returnValue($ccn));

        return $mock;
    }
}
