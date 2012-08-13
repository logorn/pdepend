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
 * @subpackage Util_Coverage
 * @author     Manuel Pichler <mapi@pdepend.org>
 * @copyright  2008-2012 Manuel Pichler. All rights reserved.
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    SVN: $Id$
 * @link       http://pdepend.org/
 */

/**
 * Test case for the {@link PHP_Depend_Util_Coverage_CloverReport} class.
 *
 * @category   QualityAssurance
 * @package    PHP_Depend
 * @subpackage Util_Coverage
 * @author     Manuel Pichler <mapi@pdepend.org>
 * @copyright  2008-2012 Manuel Pichler. All rights reserved.
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    Release: @package_version@
 * @link       http://pdepend.org/
 *
 * @covers PHP_Depend_Util_Coverage_CloverReport
 * @group  pdepend
 * @group  pdepend::util
 * @group  pdepend::util::coverage
 * @group  unittest
 * @group  2.0
 */
class PHP_Depend_Util_Coverage_CloverReportTest extends PHP_Depend_AbstractTest
{
    /**
     * testReportReturnsExpected0PercentCoverage
     *
     * @return void
     */
    public function testReportReturnsExpected0PercentCoverage()
    {
        $report   = $this->createCloverReport();
        $coverage = $report->getCoverage($this->createMethodMock(__FUNCTION__));

        self::assertEquals(0, $coverage);
    }

    /**
     * testReportReturnsExpected50PercentCoverage
     *
     * @return void
     */
    public function testReportReturnsExpected50PercentCoverage()
    {
        $report   = $this->createCloverReport();
        $coverage = $report->getCoverage($this->createMethodMock(__FUNCTION__));

        self::assertEquals(50, $coverage);
    }

    /**
     * testReportReturnsExpected100PercentCoverage
     *
     * @return void
     */
    public function testReportReturnsExpected100PercentCoverage()
    {
        $report   = $this->createCloverReport();
        $coverage = $report->getCoverage($this->createMethodMock(__FUNCTION__));

        self::assertEquals(100, $coverage);
    }

    /**
     * testNamespacedReportReturnsExpected0PercentCoverage
     *
     * @return void
     */
    public function testNamespacedReportReturnsExpected0PercentCoverage()
    {
        $report   = $this->createNamespacedCloverReport();
        $coverage = $report->getCoverage($this->createMethodMock(__FUNCTION__));

        self::assertEquals(0, $coverage);
    }

    /**
     * testNamespacedReportReturnsExpected50PercentCoverage
     *
     * @return void
     */
    public function testNamespacedReportReturnsExpected50PercentCoverage()
    {
        $report   = $this->createNamespacedCloverReport();
        $coverage = $report->getCoverage($this->createMethodMock(__FUNCTION__));

        self::assertEquals(50, $coverage);
    }

    /**
     * testNamespacedReportReturnsExpected100PercentCoverage
     *
     * @return void
     */
    public function testNamespacedReportReturnsExpected100PercentCoverage()
    {
        $report   = $this->createNamespacedCloverReport();
        $coverage = $report->getCoverage($this->createMethodMock(__FUNCTION__));

        self::assertEquals(100, $coverage);
    }

    /**
     * testGetCoverageReturnsZeroCoverageWhenNoMatchingEntryExists
     *
     * @return void
     */
    public function testGetCoverageReturnsZeroCoverageWhenNoMatchingEntryExists()
    {
        $report   = $this->createCloverReport();
        $coverage = $report->getCoverage($this->createMethodMock(__FUNCTION__));

        self::assertEquals(0, $coverage);
    }

    /**
     * Creates a clover coverage report instance.
     *
     * @return PHP_Depend_Util_Coverage_CloverReport
     */
    private function createCloverReport()
    {
        $sxml = simplexml_load_file(__DIR__ . '/_files/clover.xml');
        return new PHP_Depend_Util_Coverage_CloverReport($sxml);
    }

    /**
     * Creates a clover coverage report instance.
     *
     * @return PHP_Depend_Util_Coverage_CloverReport
     */
    private function createNamespacedCloverReport()
    {
        $sxml = simplexml_load_file(__DIR__ . '/_files/clover-namespaced.xml');
        return new PHP_Depend_Util_Coverage_CloverReport($sxml);
    }

    /**
     * Creates a mocked method instance.
     *
     * @param string $name Name of the mock method.
     *
     * @return \PHP\Depend\AST\ASTMethod
     */
    private function createMethodMock($name)
    {
        $method = $this->getMockBuilder('\PHP\Depend\AST\ASTMethod')
            ->disableOriginalConstructor()
            ->getMock();
        $method->expects($this->once())
            ->method('getFile')
            ->will($this->returnValue('/' . $name . '.php'));
        $method->expects($this->once())
            ->method('getStartLine')
            ->will($this->returnValue(1));
        $method->expects($this->once())
            ->method('getEndLine')
            ->will($this->returnValue(4));

        return $method;
    }
}
