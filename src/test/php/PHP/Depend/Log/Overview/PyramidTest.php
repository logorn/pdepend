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

namespace PHP\Depend\Log\Overview;

use \PHP\Depend\AbstractTest;
use \PHP\Depend\Log\DummyAnalyzer;

/**
 * Test case for the overview pyramid logger.
 *
 * @category  QualityAssurance
 * @author    Manuel Pichler <mapi@pdepend.org>
 * @copyright 2008-2012 Manuel Pichler. All rights reserved.
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version   Release: @package_version@
 * @link      http://pdepend.org/
 *
 * @covers \PHP\Depend\Log\Overview\Pyramid
 * @group  pdepend
 * @group  pdepend::log
 * @group  pdepend::log::overview
 * @group  unittest
 */
class PyramidTest extends AbstractTest
{
    /**
     * Tests that the logger returns the expected set of analyzers.
     *
     * @return void
     */
    public function testReturnsExceptedAnalyzers()
    {
        $logger    = new Pyramid();
        $actual    = $logger->getAcceptedAnalyzers();
        $exptected = array(
            \PHP\Depend\Metrics\Coupling\Analyzer::CLAZZ,
            \PHP\Depend\Metrics\CyclomaticComplexity\Analyzer::CLAZZ,
            \PHP\Depend\Metrics\Inheritance\Analyzer::CLAZZ,
            \PHP\Depend\Metrics\NodeCount\Analyzer::CLAZZ,
            \PHP\Depend\Metrics\NodeLoc\Analyzer::CLAZZ
        );

        self::assertEquals($exptected, $actual);
    }

    /**
     * Tests that the logger throws an exception if the log target wasn't
     * configured.
     *
     * @return void
     */
    public function testThrowsExceptionForInvalidLogTarget()
    {
        $this->setExpectedException(
            '\\PHP\\Depend\\Log\\NoLogOutputException',
            "The log target is not configured for 'PHP\\Depend\\Log\\Overview\\Pyramid'."
        );

        $logger = new Pyramid();
        $logger->close();
    }

    /**
     * Tests that the log method returns <b>false</b> for an invalid logger.
     *
     * @return void
     */
    public function testPyramidDoesNotAcceptInvalidAnalyzer()
    {
        $logger = new Pyramid();
        self::assertFalse($logger->log(new DummyAnalyzer()));
    }

    /**
     * Tests that the logger checks for the required analyzer.
     *
     * @return void
     */
    public function testCloseThrowsAnExceptionIfNoCouplingAnalyzerWasSet()
    {
        $this->markTestIncomplete('@todo 2.0');

        $this->setExpectedException(
            'RuntimeException',
            'Missing Coupling analyzer.'
        );

        $log = new Pyramid();
        $log->setLogFile(self::createRunResourceURI('_tmp_.svg'));
        $log->log($this->getCyclomaticComplexityAnalyzer());
        $log->log($this->getInheritanceAnalyzer());
        $log->log($this->getNodeCountAnalyzer());
        $log->log(new \PHP_Depend_Log_Overview_NodeLocAnalyzer());
        $log->close();
    }

    /**
     * Tests that the logger checks for the required analyzer.
     *
     * @return void
     */
    public function testCloseThrowsAnExceptionIfNoCyclomaticComplexityAnalyzerWasSet()
    {
        $this->markTestIncomplete('@todo 2.0');

        $this->setExpectedException(
            'RuntimeException',
            'Missing Cyclomatic Complexity analyzer.'
        );

        $log = new Pyramid();
        $log->setLogFile(self::createRunResourceURI('_tmp_.svg'));
        $log->log($this->getCouplingAnalyzer());
        $log->log($this->getInheritanceAnalyzer());
        $log->log($this->getNodeCountAnalyzer());
        $log->log(new \PHP_Depend_Log_Overview_NodeLocAnalyzer());
        $log->close();
    }

    /**
     * Tests that the logger checks for the required analyzer.
     *
     * @return void
     */
    public function testCloseThrowsAnExceptionIfNoInheritanceAnalyzerWasSet()
    {
        $this->markTestIncomplete('@todo 2.0');

        $this->setExpectedException(
            'RuntimeException',
            'Missing Inheritance analyzer.'
        );

        $log = new Pyramid();
        $log->setLogFile(self::createRunResourceURI('_tmp_.svg'));
        $log->log($this->getCouplingAnalyzer());
        $log->log($this->getCyclomaticComplexityAnalyzer());
        $log->log($this->getNodeCountAnalyzer());
        $log->log(new \PHP_Depend_Log_Overview_NodeLocAnalyzer());
        $log->close();
    }

    /**
     * Tests that the logger checks for the required analyzer.
     *
     * @return void
     */
    public function testCloseThrowsAnExceptionIfNoNodeCountAnalyzerWasSet()
    {
        $this->markTestIncomplete('@todo 2.0');

        $this->setExpectedException(
            'RuntimeException',
            'Missing Node Count analyzer.'
        );

        $log = new Pyramid();
        $log->setLogFile(self::createRunResourceURI('_tmp_.svg'));
        $log->log($this->getCouplingAnalyzer());
        $log->log($this->getCyclomaticComplexityAnalyzer());
        $log->log($this->getInheritanceAnalyzer());
        $log->log(new \PHP_Depend_Log_Overview_NodeLocAnalyzer());
        $log->close();
    }

    /**
     * Tests that the logger checks for the required analyzer.
     *
     * @return void
     */
    public function testCloseThrowsAnExceptionIfNoNodeLOCAnalyzerWasSet()
    {
        $this->markTestIncomplete('@todo 2.0');

        $this->setExpectedException(
            'RuntimeException',
            'Missing Node LOC analyzer.'
        );

        $log = new Pyramid();
        $log->setLogFile(self::createRunResourceURI('_tmp_.svg'));
        $log->log($this->getCouplingAnalyzer());
        $log->log($this->getCyclomaticComplexityAnalyzer());
        $log->log($this->getInheritanceAnalyzer());
        $log->log($this->getNodeCountAnalyzer());
        $log->close();
    }

    /**
     * testCollectedAndComputedValuesInOutputSVG
     *
     * @return void
     */
    public function testCollectedAndComputedValuesInOutputSVG()
    {
        $this->markTestIncomplete('@todo 2.0');

        $output = self::createRunResourceURI('temp.svg');
        if (file_exists($output)) {
            unlink($output);
        }

        $log = new Pyramid();
        $log->setLogFile($output);
        $log->log($this->getCouplingAnalyzer());
        $log->log($this->getCyclomaticComplexityAnalyzer());
        $log->log($this->getInheritanceAnalyzer());
        $log->log($this->getNodeCountAnalyzer());
        $log->log(new \PHP_Depend_Log_Overview_NodeLocAnalyzer());
        $log->close();

        self::assertFileExists($output);

        $expected = array(
            'cyclo'         => 5579,
            'loc'           => 35175,
            'nom'           => 3618,
            'noc'           => 384,
            'nop'           => 19,
            'andc'          => 0.31,
            'ahh'           => 0.12,
            'calls'         => 15128,
            'fanout'        => 8590,
            'cyclo-loc'     => 0.15,
            'loc-nom'       => 9.72,
            'nom-noc'       => 9.42,
            'noc-nop'       => 20.21,
            'fanout-calls'  => 0.56,
            'calls-nom'     => 4.18
        );

        $svg = new \DOMDocument();
        $svg->load($output);

        // TODO: Replace this loop assertion
        foreach ($expected as $name => $value) {
            $elem = $svg->getElementById("pdepend.{$name}");
            self::assertInstanceOf('DOMElement', $elem);
            self::assertEquals($value, $elem->nodeValue, null, 0.01);
        }

        unlink($output);
    }

    /**
     * Returns a mocked inheritance analyzer.
     *
     * @return \PHP\Depend\Metrics\Inheritance\Analyzer
     */
    private function getInheritanceAnalyzer()
    {
        return $this->getAnalyzerMock(
            \PHP\Depend\Metrics\Inheritance\Analyzer::CLAZZ,
            array(
                'andc'  => 0.31,
                'ahh'   => 0.12
            )
        );
    }

    /**
     * Returns a mocked coupling analyzer.
     *
     * @return \PHP\Depend\Metrics\Coupling\Analyzer
     */
    private function getCouplingAnalyzer()
    {
        return $this->getAnalyzerMock(
            \PHP\Depend\Metrics\Coupling\Analyzer::CLAZZ,
            array(
                'fanout'  => 8590,
                'calls'   => 15128
            )
        );
    }

    /**
     * Returns a mocked cyclomatic complexity analyzer.
     *
     * @return \PHP\Depend\Metrics\CyclomaticComplexity\Analyzer
     */
    private function getCyclomaticComplexityAnalyzer()
    {
        return $this->getAnalyzerMock(
            \PHP\Depend\Metrics\CyclomaticComplexity\Analyzer::CLAZZ,
            array(
                'ccn2'  => 5579
            )
        );
    }

    /**
     * Returns a mocked node count analyzer.
     *
     * @return \PHP\Depend\Metrics\NodeCount\Analyzer
     */
    private function getNodeCountAnalyzer()
    {
        return $this->getAnalyzerMock(
            \PHP\Depend\Metrics\NodeCount\Analyzer::CLAZZ,
            array(
                'nop'  => 19,
                'noc'  => 384,
                'nom'  => 2018,
                'nof'  => 1600
            )
        );
    }

    /**
     * Returns a mocked node loc analyzer.
     *
     * @return \PHP\Depend\Metrics\NodeLoc\Analyzer
     */
    private function getNodeLocAnalyzer()
    {
        return $this->getAnalyzerMock(
            \PHP\Depend\Metrics\NodeLoc\Analyzer::CLAZZ,
            array(
                'eloc'  => 35175
            )
        );
    }

    /**
     * @param string $class
     * @param array $data
     * @return \PHP\Depend\Metrics\Analyzer
     */
    private function getAnalyzerMock($class, array $data)
    {
        $analyzer = $this->getMockWithoutConstructor($class);
        $analyzer->expects($this->any())
            ->method('getProjectMetrics')
            ->will(
                $this->returnCallback(
                    function () use ($data) {
                        return $data;
                    }
                )
            );

        return $analyzer;
    }
}
