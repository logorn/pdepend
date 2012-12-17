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

namespace PHP\Depend\Log\Summary;

use \PHP\Depend\AbstractTest;
use \PHP\Depend\Log\LogProcessor;

/**
 * Test case for the xml summary log.
 *
 * @category  QualityAssurance
 * @author    Manuel Pichler <mapi@pdepend.org>
 * @copyright 2008-2012 Manuel Pichler. All rights reserved.
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version   Release: @package_version@
 * @link      http://pdepend.org/
 *
 * @covers \PHP\Depend\Log\Summary\Xml
 * @group  pdepend
 * @group  pdepend::log
 * @group  pdepend::log::summary
 * @group  unittest
 */
class XmlTest extends AbstractTest
{
    /**
     * The temporary file name for the logger result.
     *
     * @var string
     */
    private $resultFile = null;

    /**
     * Creates the package structure from a test source file.
     *
     * @return void
     */
    protected function setUp()
    {
        parent::setUp();

        $this->resultFile = self::createRunResourceURI('log-summary.xml');
    }

    /**
     * Tests that the logger returns the expected set of analyzers.
     *
     * @return void
     */
    public function testReturnsExceptedAnalyzers()
    {
        $logger = new Xml();

        $this->assertEquals(
            array(
                'PHP\Depend\Metrics\NodeAware',
                'PHP\Depend\Metrics\ProjectAware'
            ),
            $logger->getAcceptedAnalyzers()
        );
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
            '\PHP\Depend\Log\NoLogOutputException',
            "The log target is not configured for 'PHP\\Depend\\Log\\Summary\\Xml'."
        );

        $logger = new Xml();
        $logger->close();
    }

    /**
     * testLogMethodReturnsTrueForAnalyzerOfTypeProjectAware
     *
     * @return void
     */
    public function testLogMethodReturnsTrueForAnalyzerOfTypeProjectAware()
    {
        $logger = new Xml();
        $actual = $logger->log($this->getMock('\\PHP\\Depend\\Metrics\\ProjectAware'));

        $this->assertTrue($actual);
    }

    /**
     * testLogMethodReturnsTrueForAnalyzerOfTypeNodeAware
     *
     * @return void
     */
    public function testLogMethodReturnsTrueForAnalyzerOfTypeNodeAware()
    {
        $logger = new Xml();
        $actual = $logger->log($this->getMock('\\PHP\\Depend\\Metrics\\NodeAware'));

        $this->assertTrue($actual);
    }

    /**
     * Tests that {@link \PHP\Depend\Log\Summary\Xml::write()} generates the
     * expected document structure for the source, but without any applied
     * metrics.
     *
     * @return void
     */
    public function testXmlLogWithoutMetrics()
    {
        $log = new Xml();
        $log->setLogFile($this->resultFile);

        $processor = new LogProcessor();
        $processor->register($log);
        $processor->process(self::parseCodeResourceForTest());

        $log->close();

        $fileName = 'xml-log-without-metrics.xml';
        $this->assertXmlStringEqualsXmlString(
            $this->getNormalizedPathXml(__DIR__ . "/_expected/{$fileName}"),
            $this->getNormalizedPathXml($this->resultFile)
        );
    }

    /**
     * Tests that the xml logger generates the expected xml document for an
     * empty source code structure.
     *
     * @return void
     */
    public function testProjectAwareAnalyzerWithoutCode()
    {
        $metricsOne = array('interfs' => 42,
                            'cls'     => 23);
        $resultOne  = new AnalyzerProjectAwareDummy($metricsOne);

        $metricsTwo = array('ncloc' => 1742,
                            'loc'   => 4217);
        $resultTwo  = new AnalyzerProjectAwareDummy($metricsTwo);

        $log = new Xml();
        $log->setLogFile($this->resultFile);
        $log->log($resultOne);
        $log->log($resultTwo);

        $log->close();

        $fileName = 'project-aware-result-set-without-code.xml';
        $this->assertXmlStringEqualsXmlString(
            $this->getNormalizedPathXml(__DIR__ . "/_expected/{$fileName}"),
            $this->getNormalizedPathXml($this->resultFile)
        );
    }

    /**
     * testAnalyzersThatImplementProjectAndNodeAwareAsExpected
     *
     * @return void
     */
    public function testAnalyzersThatImplementProjectAndNodeAwareAsExpected()
    {
        $analyzer = new AnalyzerNodeAndProjectAwareDummy(
            array('foo' => 42, 'bar' => 23),
            array('baz' => 23, 'foobar' => 42)
        );

        $log = new Xml();
        $log->log($analyzer);
        $log->setLogFile($this->resultFile);

        $processor = new LogProcessor();
        $processor->register($log);
        $processor->process(self::parseCodeResourceForTest());

        $log->close();

        $fileName = 'node-and-project-aware-result-set.xml';
        $this->assertXmlStringEqualsXmlString(
            $this->getNormalizedPathXml(__DIR__ . "/_expected/{$fileName}"),
            $this->getNormalizedPathXml($this->resultFile)
        );
    }

    /**
     * testNodeAwareAnalyzer
     *
     * @return void
     */
    public function testNodeAwareAnalyzer()
    {
        $metricsOne = array(
            '+global#n'     => array('loc' => 42),
            'pkg1#n'        => array('loc' => 101),
            'pkg3#n'        => array('loc' => 42),
            'bar()#f'       => array('loc' => 9),
            'foo()#f'       => array('loc' => 9),
            'Bar#c'         => array('loc' => 33),
            'Bar::y()#m'    => array('loc' => 9),
            'FooBar#c'      => array('loc' => 90),
            'FooBar::x()#m' => array('loc' => 50),
            'FooBar::y()#m' => array('loc' => 30),
        );
        $metricsTwo = array(
            '+global#n'     => array('ncloc' => 23),
            'pkg1#n'        => array('ncloc' => 99),
            'pkg3#n'        => array('ncloc' => 23),
            'bar()#f'       => array('ncloc' => 7),
            'foo()#f'       => array('ncloc' => 9),
            'Bar#c'         => array('ncloc' => 20),
            'Bar::y()#m'    => array('ncloc' => 7),
            'FooBar#c'      => array('ncloc' => 80),
            'FooBar::x()#m' => array('ncloc' => 45),
            'FooBar::y()#m' => array('ncloc' => 22),
        );

        $resultOne = new AnalyzerNodeAwareDummy($metricsOne);
        $resultTwo = new AnalyzerNodeAwareDummy($metricsTwo);

        $log = new Xml();
        $log->setLogFile($this->resultFile);
        $log->log($resultOne);
        $log->log($resultTwo);

        $processor = new LogProcessor();
        $processor->register($log);
        $processor->process(self::parseCodeResourceForTest());

        $log->close();

        $fileName = 'node-aware-result-set.xml';
        $this->assertXmlStringEqualsXmlString(
            $this->getNormalizedPathXml(__DIR__ . "/_expected/{$fileName}"),
            $this->getNormalizedPathXml($this->resultFile)
        );
    }

    /**
     * Returns an xml document with normalized file paths, so that we can compare
     * the xml reports from different file system locations.
     *
     * @param string $fileName
     * @return string
     */
    protected function getNormalizedPathXml($fileName)
    {
        return preg_replace(
            array('(file\s+name="[^"]+")', '(generated="[^"]*")'),
            array('file name="' . __FILE__ . '"', 'generated=""'),
            file_get_contents($fileName)
        );
    }
}
