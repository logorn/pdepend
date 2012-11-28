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
 * Test case for the class level analyzer.
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
 * @covers PHP_Depend_Metrics_ClassLevel_Analyzer
 * @group  pdepend
 * @group  pdepend::metrics
 * @group  pdepend::metrics::classlevel
 * @group  unittest
 */
class PHP_Depend_Metrics_ClassLevel_AnalyzerTest extends PHP_Depend_Metrics_AbstractTest
{
    /**
     * Tests that the {@link PHP_Depend_Metrics_ClassLevel_Analyzer::analyzer()}
     * method fails with an exception if no cc analyzer was set.
     *
     * @return void
     * @expectedException \RuntimeException
     */
    public function testClassLevelFailsWithoutCCNAnalyzer()
    {
        $processor = new DefaultProcessor();
        $processor->register(new PHP_Depend_Metrics_ClassLevel_Analyzer());
        $processor->process(self::parseCodeResourceForTest());
    }

    /**
     * Tests that {@link PHP_Depend_Metrics_ClassLevel_Analyzer::addAnalyzer()}
     * fails for an invalid child analyzer.
     *
     * @return void
     * @expectedException \InvalidArgumentException
     */
    public function testAddAnalyzerFailsForAnInvalidAnalyzerTypeFail()
    {
        $analyzer = new PHP_Depend_Metrics_ClassLevel_Analyzer();
        $analyzer->addAnalyzer(new PHP_Depend_Metrics_CodeRank_Analyzer());
    }

    /**
     * testGetRequiredAnalyzersReturnsExpectedClassNames
     *
     * @return void
     */
    public function testGetRequiredAnalyzersReturnsExpectedClassNames()
    {
        $analyzer = new PHP_Depend_Metrics_ClassLevel_Analyzer();
        $this->assertEquals(
            array(PHP_Depend_Metrics_CyclomaticComplexity_Analyzer::CLAZZ),
            $analyzer->getRequiredAnalyzers()
        );
    }

    /**
     * testZeroInheritance
     *
     * @return array
     */
    public function testZeroInheritance()
    {
        $metrics = $this->calculateClassMetrics('DefaultClassLevelMetricSet');

        $this->assertEquals(
            array(
                'impl',
                'cis',
                'csz',
                'npm',
                'vars',
                'varsi',
                'varsnp',
                'wmc',
                'wmci',
                'wmcnp'
            ),
            array_keys($metrics)
        );

        return $metrics;
    }

    /**
     * Tests that the analyzer calculates the correct IMPL values.
     *
     * @param array $metrics
     *
     * @return void
     * @depends testZeroInheritance
     */
    public function testClassIMPLMetricZeroInheritance(array $metrics)
    {
        $this->assertEquals(4, $metrics['impl']);
    }

    /**
     * Tests that the calculated Class Interface Size(CSI) is correct.
     *
     * @param array $metrics
     *
     * @return void
     * @depends testZeroInheritance
     */
    public function testClassCISMetricZeroInheritance(array $metrics)
    {
        $this->assertEquals(2, $metrics['cis']);
    }

    /**
     * Tests that the calculated Class SiZe(CSZ) metric is correct.
     *
     * @param array $metrics
     *
     * @return void
     * @depends testZeroInheritance
     */
    public function testClassCSZMetricZeroInheritance(array $metrics)
    {
        $this->assertEquals(6, $metrics['csz']);
    }

    /**
     * testCalculateNpmMetricZeroInheritance
     *
     * @param array $metrics
     *
     * @return void
     * @depends testZeroInheritance
     */
    public function testClassNpmMetricZeroInheritance(array $metrics)
    {
        $this->assertEquals(1, $metrics['npm']);
    }

    /**
     * Tests that the analyzer calculates the correct VARS metric
     *
     * @param array $metrics
     *
     * @return void
     * @depends testZeroInheritance
     */
    public function testClassVARSMetricZeroInheritance(array $metrics)
    {
        $this->assertEquals(3, $metrics['vars']);
    }

    /**
     * Tests that the analyzer calculates the correct VARSi metric
     *
     * @param array $metrics
     *
     * @return void
     * @depends testZeroInheritance
     */
    public function testClassVARSiMetricZeroInheritance(array $metrics)
    {
        $this->assertEquals(3, $metrics['varsi']);
    }

    /**
     * Tests that the analyzer calculates the correct VARSnp metric
     *
     * @param array $metrics
     *
     * @return void
     * @depends testZeroInheritance
     */
    public function testClassVARSnpMetricZeroInheritance(array $metrics)
    {
        $this->assertEquals(1, $metrics['varsnp']);
    }

    /**
     * Tests that the analyzer calculates the correct WMC metric.
     *
     * @param array $metrics
     *
     * @return void
     * @depends testZeroInheritance
     */
    public function testClassWMCMetricZeroInheritance(array $metrics)
    {
        $this->assertEquals(3, $metrics['wmc']);
    }

    /**
     * Tests that the analyzer calculates the correct WMCi metric.
     *
     * @param array $metrics
     *
     * @return void
     * @depends testZeroInheritance
     */
    public function testClassWMCiMetricZeroInheritance(array $metrics)
    {
        $this->assertEquals(3, $metrics['wmci']);
    }

    /**
     * Tests that the analyzer calculates the correct WMCnp metric.
     *
     * @param array $metrics
     *
     * @return void
     * @depends testZeroInheritance
     */
    public function testClassWMCnpMetricZeroInheritance(array $metrics)
    {
        $this->assertEquals(1, $metrics['wmcnp']);
    }

    /**
     * testOneLevelInheritance
     *
     * @return array
     */
    public function testOneLevelInheritance()
    {
        $metrics = $this->calculateClassMetrics('ClassLevelOneLevelInherit');

        $this->assertEquals(
            array(
                'impl',
                'cis',
                'csz',
                'npm',
                'vars',
                'varsi',
                'varsnp',
                'wmc',
                'wmci',
                'wmcnp'
            ),
            array_keys($metrics)
        );

        return $metrics;
    }

    /**
     * Tests that the analyzer calculates the correct IMPL values.
     *
     * @param array $metrics
     *
     * @return void
     * @depends testOneLevelInheritance
     */
    public function testClassIMPLMetricOneLevelInheritance(array $metrics)
    {
        $this->assertEquals(3, $metrics['impl']);
    }

    /**
     * Tests that the calculated Class Interface Size(CSI) is correct.
     *
     * @param array $metrics
     *
     * @return void
     * @depends testOneLevelInheritance
     */
    public function testClassCISMetricOneLevelInheritance(array $metrics)
    {
        $this->assertEquals(3, $metrics['cis']);
    }

    /**
     * Tests that the calculated Class SiZe(CSZ) metric is correct.
     *
     * @param array $metrics
     *
     * @return void
     * @depends testOneLevelInheritance
     */
    public function testClassCSZMetricOneLevelInheritance(array $metrics)
    {
        $this->assertEquals(6, $metrics['csz']);
    }

    /**
     * testCalculateNpmMetricForEmptyClass
     *
     * @param array $metrics
     *
     * @return void
     * @depends testOneLevelInheritance
     */
    public function testClassNpmMetricOneLevelInheritance(array $metrics)
    {
        $this->assertEquals(1, $metrics['npm']);
    }

    /**
     * Tests that the analyzer calculates the correct VARS metric
     *
     * @param array $metrics
     *
     * @return void
     * @depends testOneLevelInheritance
     */
    public function testClassVARSMetricOneLevelInheritance(array $metrics)
    {
        $this->assertEquals(3, $metrics['vars']);
    }

    /**
     * Tests that the analyzer calculates the correct VARSi metric
     *
     * @param array $metrics
     *
     * @return void
     * @depends testOneLevelInheritance
     */
    public function testClassVARSiMetricOneLevelInheritance(array $metrics)
    {
        $this->assertEquals(5, $metrics['varsi']);
    }

    /**
     * Tests that the analyzer calculates the correct VARSnp metric
     *
     * @param array $metrics
     *
     * @return void
     * @depends testOneLevelInheritance
     */
    public function testClassVARSnpMetricOneLevelInheritance(array $metrics)
    {
        $this->assertEquals(2, $metrics['varsnp']);
    }

    /**
     * Tests that the analyzer calculates the correct WMC metric.
     *
     * @param array $metrics
     *
     * @return void
     * @depends testOneLevelInheritance
     */
    public function testClassWMCMetricOneLevelInheritance(array $metrics)
    {
        $this->assertEquals(6, $metrics['wmc']);
    }

    /**
     * Tests that the analyzer calculates the correct WMC metric.
     *
     * @param array $metrics
     *
     * @return void
     * @depends testOneLevelInheritance
     */
    public function testClassWMCiMetricOneLevelInheritance(array $metrics)
    {
        $this->assertEquals(10, $metrics['wmci']);
    }

    /**
     * Tests that the analyzer calculates the correct WMCnp metric.
     *
     * @param array $metrics
     *
     * @return void
     * @depends testOneLevelInheritance
     */
    public function testClassWMCnpMetricOneLevelInheritance(array $metrics)
    {
        $this->assertEquals(3, $metrics['wmcnp']);
    }

    /**
     * testTwoLevelInheritance
     *
     * @return array
     */
    public function testTwoLevelInheritance()
    {
        $metrics = $this->calculateClassMetrics('ClassLevelTwoLevelInherit');

        $this->assertEquals(
            array(
                'impl',
                'cis',
                'csz',
                'npm',
                'vars',
                'varsi',
                'varsnp',
                'wmc',
                'wmci',
                'wmcnp'
            ),
            array_keys($metrics)
        );

        return $metrics;
    }

    /**
     * Tests that the analyzer calculates the correct IMPL values.
     *
     * @param array $metrics
     *
     * @return void
     * @depends testTwoLevelInheritance
     */
    public function testClassIMPLMetricTwoLevelInheritance(array $metrics)
    {
        $this->assertEquals(4, $metrics['impl']);
    }

    /**
     * Tests that the calculated Class Interface Size(CSI) is correct.
     *
     * @param array $metrics
     *
     * @return void
     * @depends testTwoLevelInheritance
     */
    public function testClassCISMetricTwoLevelInheritance(array $metrics)
    {
        $this->assertEquals(3, $metrics['cis']);
    }

    /**
     * testCalculateCSZMetricTwoLevelInheritance
     *
     * @param array $metrics
     *
     * @return void
     * @depends testTwoLevelInheritance
     */
    public function testClassCSZMetricTwoLevelInheritance(array $metrics)
    {
        $this->assertEquals(6, $metrics['csz']);
    }

    /**
     * testCalculateNpmMetricTwoLevelInheritance
     *
     * @param array $metrics
     *
     * @return void
     * @depends testTwoLevelInheritance
     */
    public function testClassNpmMetricTwoLevelInheritance(array $metrics)
    {
        $this->assertEquals(1, $metrics['npm']);
    }

    /**
     * testCalculateVARSMetricTwoLevelInheritance
     *
     * @param array $metrics
     *
     * @return void
     * @depends testTwoLevelInheritance
     */
    public function testClassVARSMetricTwoLevelInheritance(array $metrics)
    {
        $this->assertEquals(3, $metrics['vars']);
    }

    /**
     * testCalculateVARSiMetricTwoLevelInheritance
     *
     * @param array $metrics
     *
     * @return void
     * @depends testTwoLevelInheritance
     */
    public function testClassVARSiMetricTwoLevelInheritance(array $metrics)
    {
        $this->assertEquals(6, $metrics['varsi']);
    }

    /**
     * testCalculateVARSnpMetricTwoLevelInheritance
     *
     * @param array $metrics
     *
     * @return void
     * @depends testTwoLevelInheritance
     */
    public function testClassVARSnpMetricTwoLevelInheritance(array $metrics)
    {
        $this->assertEquals(2, $metrics['varsnp']);
    }

    /**
     * Tests that the analyzer calculates the correct WMC metric.
     *
     * @param array $metrics
     *
     * @return void
     * @depends testTwoLevelInheritance
     */
    public function testClassWMCMetricTwoLevelInheritance(array $metrics)
    {
        $this->assertEquals(6, $metrics['wmc']);
    }

    /**
     * Tests that the analyzer calculates the correct WMCi metric.
     *
     * @param array $metrics
     *
     * @return void
     * @depends testTwoLevelInheritance
     */
    public function testClassWMCiMetricTwoLevelInheritance(array $metrics)
    {
        $this->assertEquals(11, $metrics['wmci']);
    }

    /**
     * Tests that the analyzer calculates the correct WMCnp metric.
     *
     * @param array $metrics
     *
     * @return void
     * @depends testTwoLevelInheritance
     */
    public function testClassWMCnpMetricTwoLevelInheritance(array $metrics)
    {
        $this->assertEquals(3, $metrics['wmcnp']);
    }

    /**
     * Analyzes the source code associated with the given test case and returns
     * a single measured metric.
     *
     * @param string $name Name of the searched metric.
     *
     * @return mixed
     */
    private function calculateClassMetric($name)
    {
        $metrics = $this->calculateClassMetrics();
        return $metrics[$name];
    }

    /**
     * Analyzes the source code associated with the calling test method and
     * returns all measured metrics.
     *
     * @param string $class
     *
     * @return mixed
     */
    private function calculateClassMetrics($class = 'Foo')
    {
        $source = self::parseTestCaseSource(self::getCallingTestMethod());

        $ccnAnalyzer = new PHP_Depend_Metrics_CyclomaticComplexity_Analyzer();
        $ccnAnalyzer->setCache(new PHP_Depend_Util_Cache_Driver_Memory());

        $processor = new DefaultProcessor();
        $processor->register($ccnAnalyzer);
        $processor->process($source);

        $analyzer = new PHP_Depend_Metrics_ClassLevel_Analyzer();
        $analyzer->addAnalyzer($ccnAnalyzer);

        $processor = new DefaultProcessor();
        $processor->register($analyzer);
        $processor->process($source);

        return $analyzer->getNodeMetrics("{$class}#c");
    }

    /**
     * testGetNodeMetricsForTrait
     *
     * @return array
     * @since 1.0.6
     */
    public function testGetNodeMetricsForTrait()
    {
        $this->markTestSkipped('TODO: 2.0');

        $metrics = $this->calculateTraitMetrics();

        $this->assertInternalType('array', $metrics);

        return $metrics;
    }

    /**
     * testReturnedMetricSetForTrait
     *
     * @param array $metrics Calculated class metrics.
     *
     * @return array
     * @since   1.0.6
     * @depends testGetNodeMetricsForTrait
     */
    public function testReturnedMetricSetForTrait(array $metrics)
    {
        $this->assertEquals(
            array(
                'impl',
                'cis',
                'csz',
                'npm',
                'vars',
                'varsi',
                'varsnp',
                'wmc',
                'wmci',
                'wmcnp'
            ),
            array_keys($metrics)
        );

        return $metrics;
    }

    /**
     * Tests that the analyzer calculates the correct IMPL values.
     *
     * @param array $metrics Calculated class metrics.
     *
     * @return void
     * @since   1.0.6
     * @depends testReturnedMetricSetForTrait
     */
    public function testCalculateIMPLMetricForTrait(array $metrics)
    {
        $this->assertEquals(0, $metrics['impl']);
    }

    /**
     * Tests that the calculated Class Interface Size(CSI) is correct.
     *
     * @param array $metrics Calculated class metrics.
     *
     * @return void
     * @since   1.0.6
     * @depends testReturnedMetricSetForTrait
     */
    public function testCalculateCISMetricForTrait(array $metrics)
    {
        $this->assertEquals(2, $metrics['cis']);
    }

    /**
     * Tests that the calculated Class SiZe(CSZ) metric is correct.
     *
     * @param array $metrics Calculated class metrics.
     *
     * @return void
     * @since   1.0.6
     * @depends testReturnedMetricSetForTrait
     */
    public function testCalculateCSZMetricForTrait(array $metrics)
    {
        $this->assertEquals(3, $metrics['csz']);
    }

    /**
     * testCalculateNpmMetricForClassWithPublicMethod
     *
     * @param array $metrics Calculated class metrics.
     *
     * @return void
     * @since   1.0.6
     * @depends testReturnedMetricSetForTrait
     */
    public function testCalculateNpmMetricForTrait(array $metrics)
    {
        $this->assertEquals(2, $metrics['npm']);
    }

    /**
     * Tests that the analyzer calculates the correct VARS metric
     *
     * @param array $metrics Calculated class metrics.
     *
     * @return void
     * @since   1.0.6
     * @depends testReturnedMetricSetForTrait
     */
    public function testCalculateVARSMetricForTrait(array $metrics)
    {
        $this->assertEquals(0, $metrics['vars']);
    }

    /**
     * Tests that the analyzer calculates the correct VARSi metric
     *
     * @param array $metrics Calculated class metrics.
     *
     * @return void
     * @since   1.0.6
     * @depends testReturnedMetricSetForTrait
     */
    public function testCalculateVARSiMetricForTrait(array $metrics)
    {
        $this->assertEquals(0, $metrics['varsi']);
    }

    /**
     * Tests that the analyzer calculates the correct VARSnp metric
     *
     * @param array $metrics Calculated class metrics.
     *
     * @return void
     * @since   1.0.6
     * @depends testReturnedMetricSetForTrait
     */
    public function testCalculateVARSnpMetricForTrait(array $metrics)
    {
        $this->assertEquals(0, $metrics['varsnp']);
    }

    /**
     * Tests that the analyzer calculates the correct WMC metric.
     *
     * @param array $metrics Calculated class metrics.
     *
     * @return void
     * @since   1.0.6
     * @depends testReturnedMetricSetForTrait
     */
    public function testCalculateWMCMetricForTrait(array $metrics)
    {
        $this->assertEquals(10, $metrics['wmc']);
    }

    /**
     * Tests that the analyzer calculates the correct WMCi metric.
     *
     * @param array $metrics Calculated class metrics.
     *
     * @return void
     * @since   1.0.6
     * @depends testReturnedMetricSetForTrait
     */
    public function testCalculateWMCiMetricForTrait(array $metrics)
    {
        $this->assertEquals(10, $metrics['wmci']);
    }

    /**
     * Tests that the analyzer calculates the correct WMCnp metric.
     *
     * @param array $metrics Calculated class metrics.
     *
     * @return void
     * @since   1.0.6
     * @depends testReturnedMetricSetForTrait
     */
    public function testCalculateWMCnpMetricForTrait(array $metrics)
    {
        $this->assertEquals(8, $metrics['wmcnp']);
    }

    /**
     * Analyzes the source code associated with the calling test method and
     * returns all measured metrics.
     *
     * @return mixed
     * @since 1.0.6
     */
    private function calculateTraitMetrics()
    {
        $ccnAnalyzer = new PHP_Depend_Metrics_CyclomaticComplexity_Analyzer();
        $ccnAnalyzer->setCache(new PHP_Depend_Util_Cache_Driver_Memory());

        $analyzer = new PHP_Depend_Metrics_ClassLevel_Analyzer();
        $analyzer->addAnalyzer($ccnAnalyzer);

        $processor = new DefaultProcessor();
        $processor->register($analyzer);
        $processor->process($this->parseCodeResourceForTest());

        return $analyzer->getNodeMetrics('Foo#t');
    }
}
