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

namespace PHP\Depend\Metrics\Inheritance;

use \PHP\Depend\Metrics\Processor\DefaultProcessor;
use PHP\Depend\Metrics\AbstractTest;

/**
 * Test case for the inheritance analyzer.
 *
 * @category  QualityAssurance
 * @author    Manuel Pichler <mapi@pdepend.org>
 * @copyright 2008-2012 Manuel Pichler. All rights reserved.
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version   Release: @package_version@
 * @link      http://pdepend.org/
 *
 * @covers \PHP\Depend\Metrics\Inheritance\Analyzer
 * @group  pdepend
 * @group  pdepend::metrics
 * @group  pdepend::metrics::inheritance
 * @group  unittest
 */
class AnalyzerTest extends AbstractTest
{
    /**
     * testGetProjectMetricsReturnsExpectedSetOfMetrics
     *
     * @return array
     */
    public function testGetProjectMetricsReturnsExpectedSetOfMetrics()
    {
        $processor = new DefaultProcessor();
        $processor->register($analyzer = new \PHP\Depend\Metrics\Inheritance\Analyzer());
        $processor->process(self::parseTestCaseSource(__METHOD__));

        $metrics = $analyzer->getProjectMetrics();
        $this->assertEquals(array('andc', 'ahh', 'maxDIT', 'leafs', 'roots'), array_keys($metrics));

        return $metrics;
    }

    /**
     * testMaxDepthOfInheritanceTree
     *
     * @param array $metrics
     * @return void
     * @depends testGetProjectMetricsReturnsExpectedSetOfMetrics
     */
    public function testMaxDepthOfInheritanceTree(array $metrics)
    {
        $this->assertEquals(4, $metrics['maxDIT']);
    }

    /**
     * Tests that the analyzer calculates the correct average number of derived
     * classes.
     *
     * @param array $metrics
     * @return void
     * @depends testGetProjectMetricsReturnsExpectedSetOfMetrics
     */
    public function testAverageNumberOfDerivedClasses(array $metrics)
    {
        $this->assertEquals(0.7368, $metrics['andc'], null, 0.0001);
    }

    /**
     * Tests that the analyzer calculates the correct average hierarchy height.
     *
     * @param array $metrics
     * @return void
     * @depends testGetProjectMetricsReturnsExpectedSetOfMetrics
     */
    public function testAverageHierarchyHeight(array $metrics)
    {
        $this->assertEquals(1, $metrics['ahh']);
    }

    /**
     * testNumberOfLeafClasses
     *
     * @param array $metrics
     * @return void
     * @depends testGetProjectMetricsReturnsExpectedSetOfMetrics
     */
    public function testNumberOfLeafClasses(array $metrics)
    {
        $this->assertEquals(11, $metrics['leafs']);
    }

    /**
     * testCalculatesExpectedNumberOfRootClasses
     *
     * @param array $metrics
     * @return void
     * @depends testGetProjectMetricsReturnsExpectedSetOfMetrics
     */
    public function testNumberOfRootClasses(array $metrics)
    {
        self::assertEquals(5, $metrics['roots']);
    }

    /**
     * testGetNodeMetricsReturnsExpectedSetOfMetrics
     *
     * @return \PHP\Depend\Metrics\Inheritance\Analyzer
     */
    public function testGetNodeMetricsReturnsExpectedSetOfMetrics()
    {
        $analyzer = new \PHP\Depend\Metrics\Inheritance\Analyzer();

        $processor = new DefaultProcessor();
        $processor->register($analyzer);
        $processor->process(self::parseTestCaseSource(__METHOD__));

        $metrics = $analyzer->getNodeMetrics('NoInheritance#c');
        $this->assertEquals(
            array(
                'dit',
                'noam',
                'nocc',
                'noom'
            ),
            array_keys($metrics)
        );

        return $analyzer;
    }

    /**
     * testCalculatesExpectedNoccMetricForClassWithoutChildren
     *
     * @param \PHP\Depend\Metrics\Inheritance\Analyzer $analyzer
     * @return void
     * @depends testGetNodeMetricsReturnsExpectedSetOfMetrics
     */
    public function testCalculatesExpectedNoccMetricForClassWithoutChildren($analyzer)
    {
        $metrics = $analyzer->getNodeMetrics('FourLevelInheritance#c');
        $this->assertEquals(0, $metrics['nocc']);
    }

    /**
     * testCalculatesExpectedNoccMetricForClassWithDirectChildren
     *
     * @param \PHP\Depend\Metrics\Inheritance\Analyzer $analyzer
     * @return void
     * @depends testGetNodeMetricsReturnsExpectedSetOfMetrics
     */
    public function testCalculatesExpectedNoccMetricForClassWithDirectChildren($analyzer)
    {
        $metrics = $analyzer->getNodeMetrics('ThreeLevelInheritance#c');
        $this->assertEquals(3, $metrics['nocc']);
    }

    /**
     * testCalculatesExpectedNoccMetricForClassWithDirectAndIndirectChildren
     *
     * @param \PHP\Depend\Metrics\Inheritance\Analyzer $analyzer
     * @return void
     * @depends testGetNodeMetricsReturnsExpectedSetOfMetrics
     */
    public function testCalculatesExpectedNoccMetricForClassWithDirectAndIndirectChildren($analyzer)
    {
        $metrics = $analyzer->getNodeMetrics('NoInheritance#c');
        $this->assertEquals(1, $metrics['nocc']);
    }

    /**
     * testCalculatesExpectedNoamMetricForClassWithDirectParent
     *
     * @param \PHP\Depend\Metrics\Inheritance\Analyzer $analyzer
     * @return void
     * @depends testGetNodeMetricsReturnsExpectedSetOfMetrics
     */
    public function testCalculatesExpectedNoamMetricForClassWithDirectParent($analyzer)
    {
        $metrics = $analyzer->getNodeMetrics('TwoLevelInheritance#c');
        $this->assertEquals(2, $metrics['noam']);
    }

    /**
     * testCalculatesExpectedNoamMetricForClassWithoutParent
     *
     * @param \PHP\Depend\Metrics\Inheritance\Analyzer $analyzer
     * @return void
     * @depends testGetNodeMetricsReturnsExpectedSetOfMetrics
     */
    public function testCalculatesExpectedNoamMetricForClassWithoutParent($analyzer)
    {
        $metrics = $analyzer->getNodeMetrics('NoInheritance#c');
        $this->assertEquals(0, $metrics['noam']);
    }

    /**
     * testCalculatesExpectedNoamMetricForClassWithIndirectParent
     *
     * @param \PHP\Depend\Metrics\Inheritance\Analyzer $analyzer
     * @return void
     * @depends testGetNodeMetricsReturnsExpectedSetOfMetrics
     */
    public function testCalculatesExpectedNoamMetricForClassWithIndirectParent($analyzer)
    {
        $metrics = $analyzer->getNodeMetrics('FourLevelInheritance#c');
        $this->assertEquals(2, $metrics['noam']);
    }

    /**
     * testCalculatesExpectedNoomMetricForClassWithoutParent
     *
     * @param \PHP\Depend\Metrics\Inheritance\Analyzer $analyzer
     * @return void
     * @depends testGetNodeMetricsReturnsExpectedSetOfMetrics
     */
    public function testCalculatesExpectedNoomMetricForClassWithoutParent($analyzer)
    {
        $metrics = $analyzer->getNodeMetrics('NoInheritance#c');
        $this->assertEquals(0, $metrics['noom']);
    }

    /**
     * testCalculatesExpectedNoomMetricForClassWithParent
     *
     * @param \PHP\Depend\Metrics\Inheritance\Analyzer $analyzer
     * @return void
     * @depends testGetNodeMetricsReturnsExpectedSetOfMetrics
     */
    public function testCalculatesExpectedNoomMetricForClassWithParent($analyzer)
    {
        $metrics = $analyzer->getNodeMetrics('OneLevelInheritance#c');
        $this->assertEquals(2, $metrics['noom']);
    }

    /**
     * testCalculatesExpectedNoomMetricForClassWithParentPrivateMethods
     *
     * @param \PHP\Depend\Metrics\Inheritance\Analyzer $analyzer
     * @return void
     * @depends testGetNodeMetricsReturnsExpectedSetOfMetrics
     */
    public function testCalculatesExpectedNoomMetricForClassWithParentPrivateMethods($analyzer)
    {
        $metrics = $analyzer->getNodeMetrics('TwoLevelInheritance#c');
        $this->assertEquals(2, $metrics['noom']);
    }

    /**
     * Tests that the analyzer calculates the correct DIT values.
     *
     * @param \PHP\Depend\Metrics\Inheritance\Analyzer $analyzer
     * @return void
     * @depends testGetNodeMetricsReturnsExpectedSetOfMetrics
     */
    public function testCalculateDITMetricNoInheritance($analyzer)
    {
        $metrics = $analyzer->getNodeMetrics('NoInheritance#c');
        $this->assertEquals(0, $metrics['dit']);
    }

    /**
     * Tests that the analyzer calculates the correct DIT values.
     *
     * @param \PHP\Depend\Metrics\Inheritance\Analyzer $analyzer
     * @return void
     * @depends testGetNodeMetricsReturnsExpectedSetOfMetrics
     */
    public function testCalculateDITMetricOneLevelInheritance($analyzer)
    {
        $metrics = $analyzer->getNodeMetrics('OneLevelInheritance#c');
        $this->assertEquals(1, $metrics['dit']);
    }

    /**
     * Tests that the analyzer calculates the correct DIT values.
     *
     * @param \PHP\Depend\Metrics\Inheritance\Analyzer $analyzer
     * @return void
     * @depends testGetNodeMetricsReturnsExpectedSetOfMetrics
     */
    public function testCalculateDITMetricTwoLevelInheritance($analyzer)
    {
        $metrics = $analyzer->getNodeMetrics('TwoLevelInheritance#c');
        $this->assertEquals(2, $metrics['dit']);
    }

    /**
     * Tests that the analyzer calculates the correct DIT values.
     *
     * @param \PHP\Depend\Metrics\Inheritance\Analyzer $analyzer
     * @return void
     * @depends testGetNodeMetricsReturnsExpectedSetOfMetrics
     */
    public function testCalculateDITMetricThreeLevelInheritance($analyzer)
    {
        $metrics = $analyzer->getNodeMetrics('ThreeLevelInheritance#c');
        $this->assertEquals(3, $metrics['dit']);
    }

    /**
     * Tests that the analyzer calculates the correct DIT values.
     *
     * @param \PHP\Depend\Metrics\Inheritance\Analyzer $analyzer
     * @return void
     * @depends testGetNodeMetricsReturnsExpectedSetOfMetrics
     */
    public function testCalculateDITMetricFourLevelInheritance($analyzer)
    {
        $metrics = $analyzer->getNodeMetrics('FourLevelInheritance#c');
        $this->assertEquals(4, $metrics['dit']);
    }

    /**
     * testCalculateDITMetricForUnknownParentIncrementsMetricWithTwo
     *
     * @param \PHP\Depend\Metrics\Inheritance\Analyzer $analyzer
     * @return void
     * @depends testGetNodeMetricsReturnsExpectedSetOfMetrics
     */
    public function testCalculateDITMetricForUnknownParentIncrementsMetricWithTwo($analyzer)
    {
        $metrics = $analyzer->getNodeMetrics('UnknownClassInheritance#c');
        $this->assertEquals(2, $metrics['dit']);
    }

    /**
     * testCalculateDITMetricForInternalParentIncrementsMetricWithTwo
     *
     * @param \PHP\Depend\Metrics\Inheritance\Analyzer $analyzer
     * @return void
     * @depends testGetNodeMetricsReturnsExpectedSetOfMetrics
     */
    public function testCalculateDITMetricForInternalParentIncrementsMetricWithTwo($analyzer)
    {
        $metrics = $analyzer->getNodeMetrics('InternalClassInheritance#c');
        $this->assertEquals(2, $metrics['dit']);
    }

    /**
     * testAnalyzerIgnoresClassesThatAreNotUserDefined
     *
     * @param \PHP\Depend\Metrics\Inheritance\Analyzer $analyzer
     * @return void
     * @depends testGetNodeMetricsReturnsExpectedSetOfMetrics
     */
    public function testAnalyzerIgnoresClassesThatAreNotUserDefined($analyzer)
    {
        $metrics = $analyzer->getNodeMetrics('MyUnknownInheritClass#c');
        $this->assertSame(array(), $metrics);
    }

    /**
     * Tests that {@link \PHP\Depend\Metrics\Inheritance\Analyzer::analyze()}
     * calculates the expected DIT values.
     *
     * @param \PHP\Depend\Metrics\Inheritance\Analyzer $analyzer
     * @return void
     * @depends testGetNodeMetricsReturnsExpectedSetOfMetrics
     */
    public function testCalculateDepthOfInheritanceForSeveralClasses($analyzer)
    {
        $expected = array(
            'NoInheritance'         => 0,
            'OneLevelInheritance'   => 1,
            'TwoLevelInheritance'   => 2,
            'ThreeLevelInheritance' => 3,
            'FourLevelInheritance'  => 4,
            'FourLevelInheritanceA' => 4,
            'FourLevelInheritanceB' => 4,
        );

        $actual = array();
        foreach (array_keys($expected) as $name) {
            $metrics       = $analyzer->getNodeMetrics("{$name}#c");
            $actual[$name] = $metrics['dit'];
        }

        $this->assertEquals($expected, $actual);
    }
}
