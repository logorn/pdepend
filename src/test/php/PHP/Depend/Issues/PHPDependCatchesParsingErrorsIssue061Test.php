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
 * @category   PHP
 * @package    PHP_Depend
 * @subpackage Issues
 * @author     Manuel Pichler <mapi@pdepend.org>
 * @copyright  2008-2012 Manuel Pichler. All rights reserved.
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    SVN: $Id$
 * @link       http://www.pdepend.org/
 */

use \PHP\Depend\Input\ExtensionFilter;
use \PHP\Depend\Log\Dummy\Logger;
use \PHP\Depend\TextUI\Command;
use \PHP\Depend\TextUI\Runner;

/**
 * Test case for the catch error ticket #61.
 *
 * @category   PHP
 * @package    PHP_Depend
 * @subpackage Issues
 * @author     Manuel Pichler <mapi@pdepend.org>
 * @copyright  2008-2012 Manuel Pichler. All rights reserved.
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    Release: @package_version@
 * @link       http://www.pdepend.org/
 *
 * @covers PHP_Depend
 * @group  pdepend
 * @group  pdepend::issues
 * @group  pdepend::textui
 * @group  unittest
 * @group 2.0
 */
class PHP_Depend_Issues_PHPDependCatchesParsingErrorsIssue061Test
    extends PHP_Depend_Issues_AbstractTest
{
    /**
     * Tests that the {@link PHP_Depend::getExceptions()} Returns a list with
     * the expected exceptions.
     *
     * @return void
     */
    public function testPHPDependReturnsExpectedExceptionInstances()
    {
        $pdepend = $this->createPDependFixture();
        $pdepend->addDirectory(self::createCodeResourceUriForTest());
        $pdepend->addFileFilter(new ExtensionFilter(array('php')));
        $pdepend->addReport(new Logger());
        $pdepend->analyze();

        $exceptions = $pdepend->getExceptions();
        self::assertStringStartsWith(
            'Unexpected token T_FUNCTION on line 7 in file ',
            $exceptions[0]->getMessage()
        );
    }

    /**
     * Tests that the {@link \PHP\Depend\TextUI\Runner::hasErrors()} method will
     * return <b>false</b> when not parsing error occured.
     *
     * @return void
     * @covers \PHP\Depend\TextUI\Runner
     */
    public function testRunnerReturnsFalseWhenNoErrorOccuredDuringTheParsingProcess()
    {
        $runner = new Runner();
        $runner->setConfiguration($this->createConfigurationFixture());
        $runner->addLogger('dummy-logger', self::createRunResourceURI('pdepend.log'));
        $runner->setSourceArguments(array(self::createCodeResourceUriForTest()));
        $runner->run();

        self::assertFalse($runner->hasParseErrors());
    }

    /**
     * Tests that the {@link \PHP\Depend\TextUI\Runner::hasErrors()} method will
     * return <b>true</b> when a parsing error occured.
     *
     * @return void
     * @covers \PHP\Depend\TextUI\Runner
     */
    public function testRunnerReturnsTrueWhenAnErrorOccuredDuringTheParsingProcess()
    {
        $runner = new Runner();
        $runner->setConfiguration($this->createConfigurationFixture());
        $runner->addLogger('dummy-logger', self::createRunResourceURI('pdepend.log'));
        $runner->setSourceArguments(array(self::createCodeResourceUriForTest()));
        $runner->run();

        self::assertTrue($runner->hasParseErrors());
    }

    /**
     * Tests that the output does not contain the error hint when the parsing
     * process was successful.
     *
     * @return void
     * @covers \PHP\Depend\TextUI\Command
     */
    public function testCommandDoesNotPrintErrorOutputOnSuccessfulParsingProcess()
    {
        $this->prepareArgv(
            array(
                '--dummy-logger=' . self::createRunResourceURI('pdepend.log'),
                self::createCodeResourceUriForTest()
            )
        );

        list(, $output) = $this->runTextUiCommand();

        self::assertNotContains('Following errors occured:', $output);
    }

    /**
     * testCommandPrintsExceptionMessageWhenAnErrorOccuredDuringTheParsingProcess
     *
     * @return void
     * @covers \PHP\Depend\TextUI\Command
     */
    public function testCommandPrintsExceptionMessageWhenAnErrorOccuredDuringTheParsingProcess()
    {
        $this->prepareArgv(
            array(
                '--dummy-logger=' . self::createRunResourceURI('pdepend.log'),
                self::createCodeResourceUriForTest()
            )
        );
        list(, $output) = $this->runTextUiCommand();

        self::assertContains('Unexpected token T_FUNCTION on line 7 in file ', $output);
    }

    /**
     * Sets a command line argument vector.
     *
     * @param array(string) $argv The temporary command line argument vector
     *
     * @return void
     */
    protected function prepareArgv($argv)
    {
        array_unshift($argv, __FILE__);

        $_SERVER['argv'] = $argv;
    }

    /**
     * Executes PHP_Depend's text ui command and returns the exit code and shell
     * output.
     *
     * @return array
     */
    protected function runTextUiCommand()
    {
        $command = new Command();

        ob_start();
        $exitCode = $command->run();
        $output   = ob_get_clean();

        return array($exitCode, $output);
    }
}
