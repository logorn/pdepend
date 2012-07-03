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

require_once dirname(__FILE__) . '/../AbstractTest.php';

/**
 * Test case for the coupling analyzer.
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
 * @covers PHP_Depend_Metrics_Coupling_Analyzer
 * @group  pdepend
 * @group  pdepend::metrics
 * @group  pdepend::metrics::coupling
 * @group  unittest
 * @group  2.0
 */
class PHP_Depend_Metrics_Coupling_AnalyzerTest extends PHP_Depend_Metrics_AbstractTest
{
    /**
     * testGetNodeMetricsReturnsExpectedSetOfMetrics
     *
     * @return PHP_Depend_Metrics_Coupling_Analyzer
     */
    public function testGetNodeMetricsReturnsExpectedSetOfMetrics()
    {
        $processor = new DefaultProcessor();
        $processor->register($analyzer = new PHP_Depend_Metrics_Coupling_Analyzer());
        $processor->process(self::parseTestCaseSource(__METHOD__));

        $metrics = $analyzer->getNodeMetrics('ClassWithoutDependencies#c');
        $this->assertEquals(array('ca', 'cbo', 'ce'), array_keys($metrics));

        return $analyzer;
    }

    /**
     * testGetNodeMetricsReturnsAnEmptyArrayByDefault
     *
     * @param PHP_Depend_Metrics_Coupling_Analyzer $analyzer
     *
     * @return void
     * @depends testGetNodeMetricsReturnsExpectedSetOfMetrics
     */
    public function testGetNodeMetricsReturnsAnEmptyArrayByDefault($analyzer)
    {
        $this->assertSame(array(), $analyzer->getNodeMetrics('ClassThatNotExists'));
    }

    /**
     * testCaMetricForClassWithoutDependencies
     *
     * @param PHP_Depend_Metrics_Coupling_Analyzer $analyzer
     *
     * @return void
     * @depends testGetNodeMetricsReturnsExpectedSetOfMetrics
     */
    public function testCaMetricForClassWithoutDependencies($analyzer)
    {
        $metrics = $analyzer->getNodeMetrics('ClassWithoutDependencies#c');
        $this->assertSame(0, $metrics['ca']);
    }

    /**
     * testCboMetricForClassWithoutDependencies
     *
     * @param PHP_Depend_Metrics_Coupling_Analyzer $analyzer
     *
     * @return void
     * @depends testGetNodeMetricsReturnsExpectedSetOfMetrics
     */
    public function testCboMetricForClassWithoutDependencies($analyzer)
    {
        $metrics = $analyzer->getNodeMetrics('ClassWithoutDependencies#c');
        $this->assertSame(0, $metrics['cbo']);
    }

    /**
     * testCeMetricForClassWithoutDependencies
     *
     * @param PHP_Depend_Metrics_Coupling_Analyzer $analyzer
     *
     * @return void
     * @depends testGetNodeMetricsReturnsExpectedSetOfMetrics
     */
    public function testCeMetricForClassWithoutDependencies($analyzer)
    {
        $metrics = $analyzer->getNodeMetrics('ClassWithoutDependencies#c');
        $this->assertSame(0, $metrics['ce']);
    }

    /**
     * testCaMetricWithPropertyDependency
     *
     * @return array
     */
    public function testCaMetricWithPropertyDependency()
    {
        $metrics = $this->getMetricsForClass('ClassWithPropertyDependency');
        $this->assertEquals(1, $metrics['ca']);

        return $metrics;
    }

    /**
     * testCboMetricWithPropertyDependency
     *
     * @param array $metrics
     *
     * @return void
     * @depends testCaMetricWithPropertyDependency
     */
    public function testCboMetricWithPropertyDependency(array $metrics)
    {
        $this->assertEquals(1, $metrics['cbo']);
    }

    /**
     * testCeMetricWithPropertyDependency
     *
     * @param array $metrics
     *
     * @return void
     * @depends testCaMetricWithPropertyDependency
     */
    public function testCeMetricWithPropertyDependency(array $metrics)
    {
        $this->assertEquals(1, $metrics['ce']);
    }

    /**
     * testCaMetricWithFunctionReturnTypeReference
     *
     * @return void
     */
    public function testCaMetricWithFunctionReturnTypeReference()
    {
        $metrics = $this->getMetricsForClass('ClassWithReturnTypeReference');
        $this->assertEquals(1, $metrics['ca']);
    }

    /**
     * testCaMetricWithMethodReturnTypeReference
     *
     * @return array
     */
    public function testCaMetricWithMethodReturnTypeReference()
    {
        $metrics = $this->getMetricsForClass('ClassMethodWithReturnTypeReference');
        $this->assertEquals(1, $metrics['ca']);

        return $metrics;
    }

    /**
     * testCboMetricWithMethodReturnTypeReference
     *
     * @param array $metrics
     *
     * @return void
     * @depends testCaMetricWithMethodReturnTypeReference
     */
    public function testCboMetricWithMethodReturnTypeReference(array $metrics)
    {
        $this->assertEquals(2, $metrics['cbo']);
    }

    /**
     * testCeMetricWithMethodReturnTypeReference
     *
     * @param array $metrics
     *
     * @return void
     * @depends testCaMetricWithMethodReturnTypeReference
     */
    public function testCeMetricWithMethodReturnTypeReference(array $metrics)
    {
        $this->assertEquals(2, $metrics['ce']);
    }

    /**
     * testCaMetricWithClassMethodExceptionReference
     *
     * @return array
     */
    public function testCaMetricWithClassMethodExceptionReference()
    {
        $metrics = $this->getMetricsForClass('ClassMethodWithExceptionReference');
        $this->assertEquals(2, $metrics['ca']);

        return $metrics;
    }

    /**
     * testCboMetricWithClassMethodExceptionReference
     *
     * @param array $metrics
     *
     * @return void
     * @depends testCaMetricWithClassMethodExceptionReference
     */
    public function testCboMetricWithClassMethodExceptionReference(array $metrics)
    {
        $this->assertEquals(3, $metrics['cbo']);
    }

    /**
     * testCeMetricWithClassMethodExceptionReference
     *
     * @param array $metrics
     *
     * @return void
     * @depends testCaMetricWithClassMethodExceptionReference
     */
    public function testCeMetricWithClassMethodExceptionReference(array $metrics)
    {
        $this->assertEquals(3, $metrics['ce']);
    }

    /**
     * testCaMetricWithFunctionExceptionReference
     *
     * @return void
     */
    public function testCaMetricWithFunctionExceptionReference()
    {
        $metrics = $this->getMetricsForClass('ExceptionReferencedByFunction');
        $this->assertEquals(2, $metrics['ca']);
    }

    /**
     * testCaMetricWithCatchStatementReference
     *
     * @return array
     */
    public function testCaMetricWithCatchStatementReference()
    {
        $metrics = $this->getMetricsForClass('ClassWithCatchStatementReference');
        $this->assertEquals(2, $metrics['ca']);

        return $metrics;
    }

    /**
     * testCboMetricWithCatchStatementReference
     *
     * @param array $metrics
     *
     * @return void
     * @depends testCaMetricWithCatchStatementReference
     */
    public function testCboMetricWithCatchStatementReference(array $metrics)
    {
        $this->assertEquals(1, $metrics['cbo']);
    }

    /**
     * testCeMetricWithCatchStatementReference
     *
     * @param array $metrics
     *
     * @return void
     * @depends testCaMetricWithCatchStatementReference
     */
    public function testCeMetricWithCatchStatementReference(array $metrics)
    {
        $this->assertEquals(1, $metrics['ce']);
    }

    /**
     * testCaWithObjectInstantiation
     *
     * @return array
     */
    public function testCaWithObjectInstantiation()
    {
        $metrics = $this->getMetricsForClass(__FUNCTION__);
        $this->assertEquals(1, $metrics['ca']);

        return $metrics;
    }

    /**
     * testCboWithObjectInstantiation
     *
     * @param array $metrics
     *
     * @return void
     * @depends testCaWithObjectInstantiation
     */
    public function testCboWithObjectInstantiation(array $metrics)
    {
        $this->assertEquals(2, $metrics['cbo']);
    }

    /**
     * testCeWithObjectInstantiation
     *
     * @param array $metrics
     *
     * @return void
     * @depends testCaWithObjectInstantiation
     */
    public function testCeWithObjectInstantiation(array $metrics)
    {
        $this->assertEquals(2, $metrics['ce']);
    }

    /**
     * testCaWithStaticMethodCall
     *
     * @return array
     */
    public function testCaWithStaticMethodCall()
    {
        $metrics = $this->getMetricsForClass(__FUNCTION__);
        $this->assertEquals(2, $metrics['ca']);

        return $metrics;
    }

    /**
     * testCboWithStaticMethodCall
     *
     * @param array $metrics
     *
     * @return void
     * @depends testCaWithStaticMethodCall
     */
    public function testCboWithStaticMethodCall(array $metrics)
    {
        $this->assertEquals(1, $metrics['cbo']);
    }

    /**
     * testCeWithStaticMethodCall
     *
     * @param array $metrics
     *
     * @return void
     * @depends testCaWithStaticMethodCall
     */
    public function testCeWithStaticMethodCall(array $metrics)
    {
        $this->assertEquals(1, $metrics['ce']);
    }

    /**
     * testCaWithReturnTypeReference
     *
     * @return array
     */
    public function testCaWithReturnTypeReference()
    {
        $metrics = $this->getMetricsForClass(__FUNCTION__);
        $this->assertEquals(1, $metrics['ca']);

        return $metrics;
    }

    /**
     * testCboWithReturnTypeReference
     *
     * @param array $metrics
     *
     * @return void
     * @depends testCaWithReturnTypeReference
     */
    public function testCboWithReturnTypeReference(array $metrics)
    {
        $this->assertEquals(2, $metrics['cbo']);
    }

    /**
     * testCeWithReturnTypeReference
     *
     * @param array $metrics
     *
     * @return void
     * @depends testCaWithReturnTypeReference
     */
    public function testCeWithReturnTypeReference(array $metrics)
    {
        $this->assertEquals(2, $metrics['ce']);
    }

    /**
     * testCaWithoutDuplicates
     *
     * @return array
     */
    public function testCaWithoutDuplicates()
    {
        $metrics = $this->getMetricsForClass(__FUNCTION__);
        $this->assertEquals(2, $metrics['ca']);

        return $metrics;
    }

    /**
     * testCboWithoutDuplicates
     *
     * @param array $metrics
     *
     * @return void
     * @depends testCaWithoutDuplicates
     */
    public function testCboWithoutDuplicates(array $metrics)
    {
        $this->assertEquals(2, $metrics['cbo']);
    }

    /**
     * testCeWithoutDuplicates
     *
     * @param array $metrics
     *
     * @return void
     * @depends testCaWithoutDuplicates
     */
    public function testCeWithoutDuplicates(array $metrics)
    {
        $this->assertEquals(2, $metrics['ce']);
    }

    /**
     * testCaForParameterTypeReferences
     *
     * @return void
     */
    public function testCaForParameterTypeReferences()
    {
        $metrics = $this->getMetricsForInterface(__FUNCTION__);
        $this->assertEquals(3, $metrics['ca']);

        return $metrics;
    }

    /**
     * testCboForParameterTypeReferences
     *
     * @param array $metrics
     *
     * @return void
     * @depends testCaForParameterTypeReferences
     */
    public function testCboForParameterTypeReferences(array $metrics)
    {
        $this->assertEquals(2, $metrics['cbo']);
    }

    /**
     * testCeForParameterTypeReferences
     *
     * @param array $metrics
     *
     * @return void
     * @depends testCaForParameterTypeReferences
     */
    public function testCeForParameterTypeReferences(array $metrics)
    {
        $this->assertEquals(2, $metrics['ce']);
    }

    /**
     * testCaForParentTypeReference
     *
     * @return array
     */
    public function testCaForParentTypeReference()
    {
        $metrics = $this->getMetricsForClass(__FUNCTION__);
        $this->assertEquals(0, $metrics['ca']);

        return $metrics;
    }

    /**
     * testCboForParentTypeReference
     *
     * @param array $metrics
     *
     * @return void
     * @depends testCaForParentTypeReference
     */
    public function testCboForParentTypeReference(array $metrics)
    {
        $this->assertEquals(0, $metrics['cbo']);
    }

    /**
     * testCeForParentTypeReference
     *
     * @param array $metrics
     *
     * @return void
     * @depends testCaForParentTypeReference
     */
    public function testCeForParentTypeReference(array $metrics)
    {
        $this->assertEquals(0, $metrics['ce']);
    }

    /**
     * testCaForChildTypeReference
     *
     * @return array
     */
    public function testCaForChildTypeReference()
    {
        $metrics = $this->getMetricsForClass(__FUNCTION__);
        $this->assertEquals(2, $metrics['ca']);

        return $metrics;
    }

    /**
     * testCboForChildTypeReference
     *
     * @param array $metrics
     *
     * @return void
     * @depends testCaForChildTypeReference
     */
    public function testCboForChildTypeReference(array $metrics)
    {
        $this->assertEquals(4, $metrics['cbo']);
    }

    /**
     * testCeForChildTypeReference
     *
     * @param array $metrics
     *
     * @return void
     * @depends testCaForChildTypeReference
     */
    public function testCeForChildTypeReference(array $metrics)
    {
        $this->assertEquals(4, $metrics['ce']);
    }

    /**
     * testCaForInstanceOfReference
     *
     * @return array
     */
    public function testCaForInstanceOfReference()
    {
        $metrics = $this->getMetricsForClass(__FUNCTION__);
        $this->assertEquals(2, $metrics['ca']);

        return $metrics;
    }

    /**
     * testCboForInstanceOfReference
     *
     * @param array $metrics
     *
     * @return void
     * @depends testCaForInstanceOfReference
     */
    public function testCboForInstanceOfReference(array $metrics)
    {
        $this->assertEquals(1, $metrics['cbo']);
    }

    /**
     * testCeForInstanceOfReference
     *
     * @param array $metrics
     *
     * @return void
     * @depends testCaForInstanceOfReference
     */
    public function testCeForInstanceOfReference(array $metrics)
    {
        $this->assertEquals(1, $metrics['ce']);
    }

    /**
     * testCaFromFunctionWithObjectInstantiation
     *
     * @return void
     */
    public function testCaFromFunctionWithObjectInstantiation()
    {
        $this->assertEquals(1, $this->getMetricForClass('ca', __FUNCTION__));
    }

    /**
     * testCaFromFunctionParameter
     *
     * @return void
     */
    public function testCaFromFunctionParameter()
    {
        $this->assertEquals(1, $this->getMetricForClass('ca', __FUNCTION__));
    }

    /**
     * testCaFromMultipleFunctions
     *
     * @return void
     */
    public function testCaFromMultipleFunctions()
    {
        $this->assertEquals(4, $this->getMetricForClass('ca', __FUNCTION__));
    }

    /**
     * testCaFromFunctionCountsTypeOnce
     *
     * @return void
     */
    public function testCaFromFunctionCountsTypeOnce()
    {
        $this->assertEquals(2, $this->getMetricForClass('ca', __FUNCTION__));
    }

    /**
     * testCboForUseInSameNamespace
     *
     * @return void
     */
    public function testCboForUseInSameNamespace()
    {
        $this->assertEquals(1, $this->getMetricForClass('cbo', 'Com\Example\ServiceManager'));
    }

    /**
     * testCboForUseInPartialSameNamespace
     *
     * @return void
     */
    public function testCboForUseInPartialSameNamespace()
    {
        $this->assertEquals(1, $this->getMetricForClass('cbo', 'Com\Example\ServiceManager'));
    }

    /**
     * testCeForUseInSameNamespace
     *
     * @return void
     */
    public function testCeForUseInSameNamespace()
    {
        $this->assertEquals(1, $this->getMetricForClass('ce', 'Com\Example\ServiceManager'));
    }

    /**
     * testCeForUseInPartialSameNamespace
     *
     * @return void
     */
    public function testCeForUseInPartialSameNamespace()
    {
        $this->assertEquals(1, $this->getMetricForClass('ce', 'Com\Example\ServiceManager'));
    }

    /**
     * Returns the metric value identified by <b>$metric</b> for a class that
     * matches the given <b>$name</b>.
     *
     * @param string $metric
     * @param string $name
     *
     * @return integer
     */
    private function getMetricForClass($metric, $name)
    {
        $metrics = $this->getMetricsForClass($name);
        return $metrics[$metric];
    }

    /**
     * Returns all metrics or a class that matches the given <b>$name</b>.
     *
     * @param string $name
     *
     * @return array
     */
    private function getMetricsForClass($name)
    {
        return $this->getMetricsForTypeWithName($name, 'c');
    }

    /**
     * Returns all metrics or an interface that matches the given <b>$name</b>.
     *
     * @param string $name
     *
     * @return array
     */
    private function getMetricsForInterface($name)
    {
        return $this->getMetricsForTypeWithName($name, 'i');
    }

    /**
     * Returns all metrics or a type that matches the given <b>$name</b> and
     * <b>$type</b>.
     *
     * @param string $name
     * @param string $type
     *
     * @return array
     */
    private function getMetricsForTypeWithName($name, $type)
    {
        $processor = new DefaultProcessor();
        $processor->register($analyzer = new PHP_Depend_Metrics_Coupling_Analyzer());
        $processor->process(self::parseTestCaseSource(self::getCallingTestMethod()));

        return $analyzer->getNodeMetrics("{$name}#{$type}");
    }

    /**
     * testAnalyzerGetProjectMetricsReturnsArrayWithExpectedKeys
     *
     * @return void
     */
    public function testAnalyzerGetProjectMetricsReturnsArrayWithExpectedKeys()
    {
        $this->assertEquals(
            array('calls', 'fanout'),
            array_keys($this->_calculateProjectMetrics())
        );
    }

    /**
     * Tests that the analyzer calculates correct fanout and call metrics for
     * functions.
     *
     * @return void
     */
    public function testAnalyzerCalculatesCorrectFunctionCoupling()
    {
        $this->assertEquals(
            array('calls' => 10, 'fanout' => 7),
            $this->_calculateProjectMetrics()
        );
    }

    /**
     * Tests that the analyzer calculates correct fanout and call metrics for
     * methods.
     *
     * @return void
     */
    public function testAnalyzerCalculatesCorrectMethodCoupling()
    {
        $this->assertEquals(
            array('calls' => 8, 'fanout' => 9),
            $this->_calculateProjectMetrics()
        );
    }

    /**
     * Tests that the analyzer calculates correct fanout and call metrics for
     * properties.
     *
     * @return void
     */
    public function testAnalyzerCalculatesCorrectPropertyCoupling()
    {
        $this->assertEquals(
            array('calls' => 0, 'fanout' => 3),
            $this->_calculateProjectMetrics()
        );
    }

    /**
     * Tests that the analyzer calculates correct fanout and call metrics for
     * properties.
     *
     * @return void
     */
    public function testAnalyzerCalculatesCorrectClassCoupling()
    {
        $this->assertEquals(
            array('calls' => 8, 'fanout' => 12),
            $this->_calculateProjectMetrics()
        );
    }

    /**
     * Tests that the analyzer calculates correct fanout and call metrics for
     * complete source.
     *
     * @return void
     */
    public function testAnalyzerCalculatesCorrectCoupling()
    {
        $this->assertEquals(
            array('calls' => 26, 'fanout' => 31),
            $this->_calculateProjectMetrics()
        );
    }

    /**
     * testGetNodeMetricsForTrait
     *
     * @return array
     * @since 1.0.6
     */
    public function testGetNodeMetricsForTrait()
    {
        $metrics = $this->_calculateTraitMetrics();
        $this->assertInternalType('array', $metrics);

        return $metrics;
    }

    /**
     * testReturnedMetricSetForTrait
     *
     * @param array $metrics Calculated coupling metrics.
     *
     * @return void
     * @since   1.0.6
     * @depends testGetNodeMetricsForTrait
     */
    public function testGetNodeMetricsForTraitReturnsExpectedMetricSet(array $metrics)
    {
        $this->assertEquals(array('ca', 'cbo', 'ce'), array_keys($metrics));
    }

    /**
     * testCalculateCEMetricForTrait
     *
     * @param array $metrics Calculated coupling metrics.
     *
     * @return void
     * @since   1.0.6
     * @depends testGetNodeMetricsForTrait
     */
    public function testCalculateCEMetricForTrait(array $metrics)
    {
        $this->assertEquals(4, $metrics['ce']);
    }

    /**
     * testCalculateCBOMetricForTrait
     *
     * @param array $metrics Calculated coupling metrics.
     *
     * @return void
     * @since   1.0.6
     * @depends testGetNodeMetricsForTrait
     */
    public function testCalculateCBOMetricForTrait(array $metrics)
    {
        $this->assertEquals(4, $metrics['cbo']);
    }

    /**
     * testCalculateCAMetricForTrait
     *
     * @param array $metrics Calculated coupling metrics.
     *
     * @return void
     * @since   1.0.6
     * @depends testGetNodeMetricsForTrait
     */
    public function testCalculateCAMetricForTrait(array $metrics)
    {
        $this->assertEquals(0, $metrics['ca']);
    }

    /**
     * testGetProjectMetricsForTrait
     *
     * @return array
     * @since 1.0.6
     */
    public function testGetProjectMetricsForTrait()
    {
        $this->markTestSkipped('TODO 2.0');

        $analyzer = new PHP_Depend_Metrics_Coupling_Analyzer();
        $analyzer->analyze($this->parseCodeResourceForTest());

        $metrics = $analyzer->getProjectMetrics();
        $this->assertInternalType('array', $metrics);

        return $metrics;
    }

    /**
     * testGetProjectMetricsForTraitReturnsExpectedMetricSet
     *
     * @param array $metrics Calculated coupling metrics.
     *
     * @return void
     * @since   1.0.6
     * @depends testGetProjectMetricsForTrait
     */
    public function testGetProjectMetricsForTraitReturnsExpectedMetricSet(array $metrics)
    {
        $this->assertEquals(array('calls', 'fanout'), array_keys($metrics));
    }

    /**
     * testCalculateCallsMetricForTrait
     *
     * @param array $metrics Calculated coupling metrics.
     *
     * @return void
     * @since   1.0.6
     * @depends testGetProjectMetricsForTrait
     */
    public function testCalculateCallsMetricForTrait(array $metrics)
    {
        $this->assertEquals(7, $metrics['calls']);
    }

    /**
     * testCalculateFanoutMetricForTrait
     *
     * @param array $metrics Calculated coupling metrics.
     *
     * @return void
     * @since   1.0.6
     * @depends testGetProjectMetricsForTrait
     */
    public function testCalculateFanoutMetricForTrait(array $metrics)
    {
        $this->assertEquals(4, $metrics['fanout']);
    }

    /**
     * Analyzes the source code associated with the calling test method and
     * returns all measured metrics.
     *
     * @return mixed
     * @since 1.0.6
     */
    private function _calculateTraitMetrics()
    {
        $this->markTestSkipped('TODO 2.0');
        $packages = $this->parseCodeResourceForTest();
        $package  = $packages->current();

        $analyzer = new PHP_Depend_Metrics_Coupling_Analyzer();
        $analyzer->analyze($packages);

        return $analyzer->getNodeMetrics($package->getTraits()->current());
    }

    /**
     * Tests that the analyzer calculates the expected call count.
     *
     * @param string  $testCase File with test source.
     * @param integer $calls    Number of expected calls.
     * @param integer $fanout   Expected fanout value.
     *
     * @return void
     * @dataProvider dataProviderAnalyzerCalculatesExpectedCallCount
     */
    public function testAnalyzerCalculatesExpectedCallCount($testCase, $calls, $fanout)
    {
        $expected = array('calls'  => $calls, 'fanout' => $fanout);
        $actual   = $this->_calculateProjectMetrics($testCase);

        $this->assertEquals($expected, $actual);
    }

    /**
     * Parses the source code for the currently calling test method and returns
     * the calculated project metrics.
     *
     * @param string $testCase Optional name of the calling test case.
     *
     * @return array(string=>mixed)
     * @since 0.10.2
     */
    private function _calculateProjectMetrics($testCase = null)
    {
        $testCase = ($testCase ? $testCase : self::getCallingTestMethod());

        $processor = new DefaultProcessor();
        $processor->register($analyzer = new PHP_Depend_Metrics_Coupling_Analyzer());
        $processor->process(self::parseTestCaseSource($testCase));

        return $analyzer->getProjectMetrics();
    }

    /**
     * Data provider that returns different test files and the corresponding
     * invocation count value.
     *
     * @return array
     */
    public static function dataProviderAnalyzerCalculatesExpectedCallCount()
    {
        return array(
            array(__METHOD__ . '#01', 0, 0),
            array(__METHOD__ . '#02', 0, 0),
            array(__METHOD__ . '#03', 0, 0),
            array(__METHOD__ . '#04', 1, 0),
            array(__METHOD__ . '#05', 1, 0),
            array(__METHOD__ . '#06', 2, 0),
            array(__METHOD__ . '#07', 1, 0),
            array(__METHOD__ . '#08', 1, 0),
            array(__METHOD__ . '#09', 1, 0),
            array(__METHOD__ . '#10', 2, 0),
            array(__METHOD__ . '#11', 2, 0),
            array(__METHOD__ . '#12', 1, 1),
            array(__METHOD__ . '#13', 0, 1),
            array(__METHOD__ . '#14', 0, 1),
            array(__METHOD__ . '#15', 1, 1),
            array(__METHOD__ . '#16', 2, 1),
            array(__METHOD__ . '#17', 4, 2),
            array(__METHOD__ . '#18', 1, 0),
            array(__METHOD__ . '#19', 1, 1),
        );
    }
}
