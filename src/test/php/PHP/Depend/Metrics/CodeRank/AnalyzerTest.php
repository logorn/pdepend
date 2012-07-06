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
 * @category  QualityAssurance
 * @package   PHP_Depend
 * @author    Manuel Pichler <mapi@pdepend.org>
 * @copyright 2008-2012 Manuel Pichler. All rights reserved.
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version   SVN: $Id$
 * @link      http://pdepend.org/
 */

use \PHP\Depend\Metrics\Processor\DefaultProcessor;

/**
 * Test case for the code metric analyzer class.
 *
 * @category  QualityAssurance
 * @package   PHP_Depend
 * @author    Manuel Pichler <mapi@pdepend.org>
 * @copyright 2008-2012 Manuel Pichler. All rights reserved.
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version   Release: @package_version@
 * @link      http://pdepend.org/
 *
 * @covers PHP_Depend_Metrics_CodeRank_Analyzer
 * @group  pdepend
 * @group  pdepend::metrics
 * @group  pdepend::metrics::coderank
 * @group  unittest
 * @group  2.0
 */
class PHP_Depend_Metrics_CodeRank_AnalyzerTest extends PHP_Depend_Metrics_AbstractTest
{
    /**
     * testCodeRankOfSimpleInheritanceExample
     *
     * @return void
     */
    public function testCodeRankOfSimpleInheritanceExample()
    {
        $this->assertCodeRank(
            array(
                'Foo#c' => 0.15,
                'Bar#i' => 0.2775,
            )
        );
    }

    /**
     * testReverseCodeRankOfSimpleInheritanceExample
     *
     * @return void
     */
    public function testReverseCodeRankOfSimpleInheritanceExample()
    {
        $this->assertReverseCodeRank(
            array(
                'Foo#c' => 0.2775,
                'Bar#i' => 0.15,
            )
        );
    }

    /**
     * testCodeRankOfNamespacedSameNamePropertyExample
     *
     * @return void
     */
    public function testCodeRankOfNamespacedSameNamePropertyExample()
    {
        $this->assertCodeRank(
            array('bar\Foo#c'  => 0.15),
            array('coderank-mode' => array('property'))
        );
    }

    /**
     * testReverseCodeRankOfNamespacedSameNamePropertyExample
     *
     * @return void
     */
    public function testReverseCodeRankOfNamespacedSameNamePropertyExample()
    {
        $this->assertReverseCodeRank(
            array('bar\Foo#c' => 0.2775),
            array('coderank-mode' => array('property'))
        );
    }

    /**
     * testCodeRankOfNamespacedSameNameMethodParamExample
     *
     * @return void
     */
    public function testCodeRankOfNamespacedSameNameMethodParamExample()
    {
        $this->assertCodeRank(
            array('bar\Foo#c' => 0.15),
            array('coderank-mode' => array('method'))
        );
    }

    /**
     * testReverseCodeRankOfNamespacedSameNameMethodParamExample
     *
     * @return void
     */
    public function testReverseCodeRankOfNamespacedSameNameMethodParamExample()
    {
        $this->assertReverseCodeRank(
            array('bar\Foo#c' => 0.2775),
            array('coderank-mode' => array('method'))
        );
    }

    /**
     * testCodeRankOfNamespacedSameNameMethodReturnExample
     *
     * @return void
     */
    public function testCodeRankOfNamespacedSameNameMethodReturnExample()
    {
        $this->assertCodeRank(
            array('bar\baz\Foo#c' => 0.15),
            array('coderank-mode' => array('method'))
        );
    }

    /**
     * testReverseCodeRankOfNamespacedSameNameMethodReturnExample
     *
     * @return void
     */
    public function testReverseCodeRankOfNamespacedSameNameMethodReturnExample()
    {
        $this->assertReverseCodeRank(
            array('bar\baz\Foo#c' => 0.2775),
            array('coderank-mode' => array('method'))
        );
    }

    /**
     * testCodeRankOfNamespacedSameNameMethodExceptionExample
     *
     * @return void
     */
    public function testCodeRankOfNamespacedSameNameMethodExceptionExample()
    {
        $this->assertCodeRank(
            array('foo\bar\baz\Foo#c' => 0.15),
            array('coderank-mode' => array('method'))
        );
    }

    /**
     * testReverseCodeRankOfNamespacedSameNameMethodExceptionExample
     *
     * @return void
     */
    public function testReverseCodeRankOfNamespacedSameNameMethodExceptionExample()
    {
        $this->assertReverseCodeRank(
            array('foo\bar\baz\Foo#c' => 0.2775),
            array('coderank-mode' => array('method'))
        );
    }

    /**
     * testCodeRankOfNamespacedSameNameInheritanceExample
     *
     * @return void
     */
    public function testCodeRankOfNamespacedSameNameInheritanceExample()
    {
        $this->assertCodeRank(array('bar\Foo#c'  => 0.15));
    }

    /**
     * testReverseCodeRankOfNamespacedSameNameInheritanceExample
     *
     * @return void
     */
    public function testReverseCodeRankOfNamespacedSameNameInheritanceExample()
    {
        $this->assertReverseCodeRank(array('bar\Foo#c'  => 0.2775));
    }

    /**
     * testCodeRankOfOrderExampleWithInheritanceAndMethodStrategy
     *
     * @return void
     */
    public function testCodeRankOfOrderExampleWithInheritanceAndMethodStrategy()
    {
        $this->assertCodeRank(
            array(
                'BCollection#i'   => 0.58637,
                'BList#i'         => 0.51338,
                'AbstractList#c'  => 0.2775,
                'ArrayList#c'     => 0.15,
                'Order#c'         => 0.15,
            ),
            array('coderank-mode' => array('inheritance', 'method'))
        );
    }

    /**
     * testReverseCodeRankOfOrderExampleWithInheritanceAndMethodStrategy
     *
     * @return void
     */
    public function testReverseCodeRankOfOrderExampleWithInheritanceAndMethodStrategy()
    {
        $this->assertReverseCodeRank(
            array(
                'BCollection#i'   => 0.15,
                'BList#i'         => 0.2775,
                'AbstractList#c'  => 0.26794,
                'ArrayList#c'     => 0.37775,
                'Order#c'         => 0.26794,
            ),
            array('coderank-mode' => array('inheritance', 'method'))
        );
    }

    /**
     * testCodeRankOfOrderExampleWithInheritanceAndPropertyStrategy
     *
     * @return void
     */
    public function testCodeRankOfOrderExampleWithInheritanceAndPropertyStrategy()
    {
        $this->assertCodeRank(
            array(
                'BCollection#i'   => 0.58637,
                'BList#i'         => 0.51338,
                'AbstractList#c'  => 0.2775,
                'ArrayList#c'     => 0.15,
                'Order#c'         => 0.15,
            ),
            array('coderank-mode' => array('inheritance', 'property'))
        );
    }

    /**
     * testReverseCodeRankOfOrderExampleWithInheritanceAndPropertyStrategy
     *
     * @return void
     */
    public function testReverseCodeRankOfOrderExampleWithInheritanceAndPropertyStrategy()
    {
        $this->assertReverseCodeRank(
            array(
                'BCollection#i'   => 0.15,
                'BList#i'         => 0.2775,
                'AbstractList#c'  => 0.26794,
                'ArrayList#c'     => 0.37775,
                'Order#c'         => 0.26794,
            ),
            array('coderank-mode' => array('inheritance', 'property'))
        );
    }

    /**
     * testCodeRankOfInternalInterfaceExample
     *
     * @return void
     */
    public function testCodeRankOfInternalInterfaceExample()
    {
        $this->assertCodeRank(
            array(
                'BList#i'         => 0.51338,
                'AbstractList#c'  => 0.2775,
                'ArrayList#c'     => 0.15,
                'Order#c'         => 0.15,
            ),
            array('coderank-mode' => array('inheritance', 'method', 'property'))
        );
    }

    /**
     * testReverseCodeRankOfInternalInterfaceExample
     *
     * @return void
     */
    public function testReverseCodeRankOfInternalInterfaceExample()
    {
        $this->assertReverseCodeRank(
            array(
                'BList#i'         => 0.2775,
                'AbstractList#c'  => 0.26794,
                'ArrayList#c'     => 0.37775,
                'Order#c'         => 0.26794,
            ),
            array('coderank-mode' => array('inheritance', 'method', 'property'))
        );
    }

    /**
     * Tests the result of the class rank calculation against previous computed
     * values.
     *
     * @return void
     */
    public function testGetNodeMetrics()
    {
        $metrics = array(
            'package1#n'    => array('cr'  => 0.2775, 'rcr'  => 0.385875),
            'package2#n'    => array('cr'  => 0.15, 'rcr'  => 0.47799375),
            'package3#n'    => array('cr'  => 0.385875, 'rcr'  => 0.2775),
            '+global#n'     => array('cr'  => 0.47799375, 'rcr'  => 0.15),
            'pkg1Foo#c'     => array('cr'  => 0.15, 'rcr'  => 0.181875),
            'pkg2FooI#i'    => array('cr'  => 0.15, 'rcr'  => 0.181875),
            'pkg2Bar#c'     => array('cr'  => 0.15, 'rcr'  => 0.1755),
            'pkg2Foobar#c'  => array('cr'  => 0.15, 'rcr'  => 0.1755),
            'pkg1Barfoo#c'  => array('cr'  => 0.15, 'rcr'  => 0.207375),
            'pkg2Barfoo#c'  => array('cr'  => 0.15, 'rcr'  => 0.207375),
            'pkg1Foobar#c'  => array('cr'  => 0.15, 'rcr'  => 0.411375),
            'pkg1FooI#i'    => array('cr'  => 0.5325, 'rcr'  => 0.15),
            'pkg1Bar#c'     => array('cr'  => 0.59625, 'rcr'  => 0.15),
            'pkg3FooI#i'    => array('cr'  => 0.21375, 'rcr'  => 0.2775),
            'Iterator#i'    => array('cr'  => 0.3316875, 'rcr'  => 0.15),
        );

        $this->assertEquals(
            $metrics,
            $this->getMetrics(
                array_keys($metrics)
            ),
            '',
            0.00005
        );
    }

    /**
     * Tests that {@link PHP_Depend_Metrics_CodeRank_Analyzer::getNodeMetrics()}
     * returns an empty <b>array</b> for an unknown identifier.
     *
     * @return void
     */
    public function testGetNodeMetricsInvalidIdentifier()
    {
        $this->assertSame(
            array('MyClass' => array()),
            $this->getMetrics(array('MyClass'))
        );
    }

    /**
     * Asserts the regular code rank,
     *
     * @param array $expected
     * @param array $options
     * @return void
     */
    public function assertCodeRank(array $expected, array $options = array())
    {
        $this->assertEquals(
            $expected,
            $this->getMetric(
                'cr',
                array_keys($expected),
                $options
            ),
            '',
            0.00005
        );
    }

    /**
     * Asserts the reverse code rank,
     *
     * @param array $expected
     * @param array $options
     * @return void
     */
    public function assertReverseCodeRank($expected, array $options = array())
    {
        $this->assertEquals(
            $expected,
            $this->getMetric(
                'rcr',
                array_keys($expected),
                $options
            ),
            '',
            0.00005
        );
    }

    /**
     * Returns a set of metrics for the given id array.
     *
     * @param string $metric
     * @param array $ids
     * @param array $options
     * @return array
     */
    private function getMetric($metric, array $ids, array $options = array())
    {
        foreach ($this->getMetrics($ids, $options) as $id => $metrics) {
            $rank[$id] = $metrics[$metric];
        }
        return $rank;
    }

    /**
     * Returns all metrics for the given list of node ids.
     *
     * @param array $ids
     * @param array $options
     * @return array
     */
    private function getMetrics(array $ids, array $options = array())
    {
        $analyzer = new PHP_Depend_Metrics_CodeRank_Analyzer($options);

        $processor = new DefaultProcessor();
        $processor->register($analyzer);
        $processor->process(self::parseCodeResourceForTest());

        $metrics = array();
        foreach ($ids as $id) {
            $metrics[$id] = $analyzer->getNodeMetrics($id);
        }
        return $metrics;
    }
}
