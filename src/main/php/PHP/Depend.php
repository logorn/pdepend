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

/**
 * PHP_Depend analyzes php class files and generates metrics.
 *
 * The PHP_Depend is a php port/adaption of the Java class file analyzer
 * <a href="http://clarkware.com/software/JDepend.html">JDepend</a>.
 *
 * @category  QualityAssurance
 * @package   PHP_Depend
 * @author    Manuel Pichler <mapi@pdepend.org>
 * @copyright 2008-2012 Manuel Pichler. All rights reserved.
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version   Release: @package_version@
 * @link      http://pdepend.org/
 */
class PHP_Depend
{
    /**
     * Marks the storage used for runtime tokens.
     */
    const TOKEN_STORAGE = 1;

    /**
     * Marks the storag engine used for parser artifacts.
     */
    const PARSER_STORAGE = 2;

    /**
     * The system configuration.
     *
     * @var PHP_Depend_Util_Configuration
     * @since 0.10.0
     */
    protected $configuration = null;

    /**
     * List of source directories.
     *
     * @var array(string) $_directories
     */
    private $_directories = array();

    /**
     * List of source code file names.
     *
     * @var array(string) $_files
     */
    private $_files = array();

    /**
     * List of all registered loggers.
     *
     * @var PHP_Depend_Log_Logger[]
     */
    private $_loggers = array();

    /**
     * A composite filter for input files.
     *
     * @var PHP_Depend_Input_CompositeFilter $_fileFilter
     */
    private $_fileFilter = null;

    /**
     * Should the parse ignore doc comment annotations?
     *
     * @var boolean $_withoutAnnotations
     */
    private $_withoutAnnotations = false;

    /**
     * List or registered listeners.
     *
     * @var PHP_Depend_ProcessListener[]
     */
    private $_listeners = array();

    /**
     * List of analyzer options.
     *
     * @var array(string=>mixed) $_options
     */
    private $_options = array();

    /**
     * List of all {@link PHP_Depend_Parser_Exception} that were caught during
     * the parsing process.
     *
     * @var PHP_Depend_Parser_Exception[]
     */
    private $_parseExceptions = array();

    /**
     * The configured cache factory.
     *
     * @var PHP_Depend_Util_Cache_Factory
     * @since 1.0.0
     */
    private $_cacheFactory;

    /**
     * Constructs a new php depend facade.
     *
     * @param PHP_Depend_Util_Configuration $configuration The system configuration.
     */
    public function __construct(PHP_Depend_Util_Configuration $configuration)
    {
        $this->configuration = $configuration;

        $this->_fileFilter = new PHP_Depend_Input_CompositeFilter();

        $this->_cacheFactory = new PHP_Depend_Util_Cache_Factory($configuration);
    }

    /**
     * Adds the specified directory to the list of directories to be analyzed.
     *
     * @param string $directory The php source directory.
     *
     * @return void
     */
    public function addDirectory($directory)
    {
        $dir = realpath($directory);

        if (!is_dir($dir)) {
            throw new InvalidArgumentException(
                "Invalid directory '{$directory}' added."
            );
        }

        $this->_directories[] = $dir;
    }

    /**
     * Adds a single source code file to the list of files to be analysed.
     *
     * @param string $file The source file name.
     *
     * @return void
     */
    public function addFile($file)
    {
        $fileName = realpath($file);

        if (!is_file($fileName)) {
            throw new InvalidArgumentException(
                sprintf('The given file "%s" does not exist.', $file)
            );
        }

        $this->_files[] = $fileName;
    }

    /**
     * Adds a logger to the output list.
     *
     * @param PHP_Depend_Log_Logger $logger
     *
     * @return void
     */
    public function addLogger(PHP_Depend_Log_Logger $logger)
    {
        $this->_loggers[] = $logger;
    }

    /**
     * Adds a new input/file filter.
     *
     * @param PHP_Depend_Input_FilterI $filter New input/file filter instance.
     *
     * @return void
     */
    public function addFileFilter(PHP_Depend_Input_FilterI $filter)
    {
        $this->_fileFilter->append($filter);
    }

    /**
     * Sets analyzer options.
     *
     * @param array(string=>mixed) $options The analyzer options.
     *
     * @return void
     */
    public function setOptions(array $options = array())
    {
        $this->_options = $options;
    }

    /**
     * Should the parse ignore doc comment annotations?
     *
     * @return void
     */
    public function setWithoutAnnotations()
    {
        $this->_withoutAnnotations = true;
    }

    /**
     * Adds a process listener.
     *
     * @param PHP_Depend_ProcessListener $listener The listener instance.
     *
     * @return void
     */
    public function addProcessListener(PHP_Depend_ProcessListener $listener)
    {
        if (in_array($listener, $this->_listeners, true) === false) {
            $this->_listeners[] = $listener;
        }
    }

    /**
     * Analyzes the registered directories and returns the collection of
     * analyzed packages.
     *
     * @return PHP_Depend_Code_NodeIterator
     */
    public function analyze()
    {
        $this->process();
    }

    /**
     * Returns an <b>array</b> with all {@link PHP_Depend_Parser_Exception} that
     * were caught during the parsing process.
     *
     * @return array(PHP_Depend_Parser_Exception)
     */
    public function getExceptions()
    {
        return $this->_parseExceptions;
    }

    /**
     * Send the start parsing process event.
     *
     * @param PHP_Depend_Parser $builder The used node builder instance.
     *
     * @return void
     */
    protected function fireStartParseProcess(PHP_Depend_Parser $builder)
    {
        foreach ($this->_listeners as $listener) {
            $listener->startParseProcess($builder);
        }
    }

    /**
     * Send the end parsing process event.
     *
     * @param PHP_Depend_Parser $builder The used node builder instance.
     *
     * @return void
     */
    protected function fireEndParseProcess(PHP_Depend_Parser $builder)
    {
        foreach ($this->_listeners as $listener) {
            $listener->endParseProcess($builder);
        }
    }

    /**
     * Sends the start file parsing event.
     *
     * @param PHP_Depend_Tokenizer $tokenizer The used tokenizer instance.
     *
     * @return void
     */
    protected function fireStartFileParsing(PHP_Depend_Tokenizer $tokenizer)
    {
        foreach ($this->_listeners as $listener) {
            $listener->startFileParsing($tokenizer);
        }
    }

    /**
     * Sends the end file parsing event.
     *
     * @param PHP_Depend_Tokenizer $tokenizer The used tokenizer instance.
     *
     * @return void
     */
    protected function fireEndFileParsing(PHP_Depend_Tokenizer $tokenizer)
    {
        foreach ($this->_listeners as $listener) {
            $listener->endFileParsing($tokenizer);
        }
    }

    /**
     * Sends the start analyzing process event.
     *
     * @return void
     */
    protected function fireStartAnalyzeProcess()
    {
        foreach ($this->_listeners as $listener) {
            $listener->startAnalyzeProcess();
        }
    }

    /**
     * Sends the end analyzing process event.
     *
     * @return void
     */
    protected function fireEndAnalyzeProcess()
    {
        foreach ($this->_listeners as $listener) {
            $listener->endAnalyzeProcess();
        }
    }

    /**
     * Sends the start log process event.
     *
     * @return void
     */
    protected function fireStartLogProcess()
    {
        foreach ($this->_listeners as $listener) {
            $listener->startLogProcess();
        }
    }

    /**
     * Sends the end log process event.
     *
     * @return void
     */
    protected function fireEndLogProcess()
    {
        foreach ($this->_listeners as $listener) {
            $listener->endLogProcess();
        }
    }

    private function process()
    {
        $compilationUnits = $this->processParsing();

        $this->processLogging(
            $compilationUnits,
            $this->processAnalyzing($compilationUnits)
        );
    }

    /**
     * This method performs the parsing process of all source files.
     *
     * This method will return an array with all processed compilation units.
     *
     * @return PHP_Depend_AST_CompilationUnit[]
     * @todo 2.0 Replace PHPParser_Error with custom exception
     * @todo 2.0 What should we do with ignoreAnnotations?
     */
    private function processParsing()
    {
        $tokenizer = new PHP_Depend_Tokenizer_VersionAll();
        $parser    = new PHP_Depend_Parser($tokenizer);

        // Reset list of thrown exceptions
        $this->_parseExceptions = array();

        $compilationUnits = array();

        $this->fireStartParseProcess($parser);

        ini_set('xdebug.max_nesting_level', $this->configuration->parser->nesting);

        foreach ($this->_createFileIterator() as $file) {
            //if ($this->_withoutAnnotations === true) {
            //    $parser->setIgnoreAnnotations();
            //}

            $this->fireStartFileParsing($tokenizer);

            try {
                $compilationUnits[] = $parser->parse($file);
            } catch (PHPParser_Error $e) {
                $this->_parseExceptions[] = $e;
            }

            $this->fireEndFileParsing($tokenizer);
        }

        ini_restore('xdebug.max_nesting_level');

        $this->fireEndParseProcess($parser);

        return $compilationUnits;
    }

    /**
     * This method performs the analysing process of the parsed source files. It
     * creates the required analyzers for the registered listeners and then
     * applies them to the source tree.
     *
     * @param PHP_Depend_AST_CompilationUnit[] $compilationUnits
     *
     * @return PHP_Depend_Metrics_Analyzer[]
     */
    private function processAnalyzing(array $compilationUnits)
    {
        $analyzerLoader = $this->_createAnalyzerLoader($this->_options);

        $this->fireStartAnalyzeProcess();

        ini_set('xdebug.max_nesting_level', $this->configuration->parser->nesting);

        $processor = new PHP_Depend_Metrics_Processor();

        foreach ($analyzerLoader as $analyzer) {
            $processor->register($analyzer);
        }

        $processor->process($compilationUnits);

        ini_restore('xdebug.max_nesting_level');

        $this->fireEndAnalyzeProcess();

        return iterator_to_array($analyzerLoader->getIterator());
    }

    /**
     * @param PHP_Depend_AST_CompilationUnit[] $compilationUnits
     * @param PHP_Depend_Metrics_Analyzer[]    $analyzers
     *
     * @return void
     */
    private function processLogging(array $compilationUnits, array $analyzers)
    {
        $this->fireStartLogProcess();

        $processor = new PHP_Depend_Log_Processor();
        foreach ($this->_loggers as $logger) {
            if ($logger instanceof PHP_Depend_Log_CodeAware) {
                $processor->register($logger);
            }

            foreach ($analyzers as $analyzer) {
                $logger->log($analyzer);
            }
        }

        $processor->process($compilationUnits);

        foreach ($this->_loggers as $logger) {
            $logger->close();
        }

        $this->fireEndLogProcess();
    }

    /**
     * This method will initialize all code analysers and register the
     * interested listeners.
     *
     * @param PHP_Depend_Metrics_AnalyzerLoader $analyzerLoader The used loader
     *                                                          instance for all code analysers.
     *
     * @return PHP_Depend_Metrics_AnalyzerLoader
     */
    private function _initAnalyseListeners(
        PHP_Depend_Metrics_AnalyzerLoader $analyzerLoader
    )
    {
        foreach ($analyzerLoader as $analyzer) {
            foreach ($this->_listeners as $listener) {
                $analyzer->addAnalyzeListener($listener);

                if ($analyzer instanceof PHP_Depend_VisitorI) {
                    $analyzer->addVisitListener($listener);
                }
            }
        }

        return $analyzerLoader;
    }

    /**
     * This method will create an iterator instance which contains all files
     * that are part of the parsing process.
     *
     * @return Iterator
     */
    private function _createFileIterator()
    {
        if (count($this->_directories) === 0 && count($this->_files) === 0) {
            throw new RuntimeException('No source directory and file set.');
        }

        $fileIterator = new AppendIterator();
        $fileIterator->append(new ArrayIterator($this->_files));

        foreach ($this->_directories as $directory) {
            $fileIterator->append(
                new PHP_Depend_Input_Iterator(
                    new RecursiveIteratorIterator(
                        new RecursiveDirectoryIterator($directory . '/')
                    ),
                    $this->_fileFilter,
                    $directory
                )
            );
        }

        // TODO: It's important to validate this behavior, imho there is something
        //       wrong in the iterator code used above.
        // Strange: why is the iterator not unique and why does this loop fix it?
        $files = array();
        foreach ($fileIterator as $file) {
            if (is_string($file)) {
                $files[$file] = $file;
            } else {
                $pathname         = realpath($file->getPathname());
                $files[$pathname] = $pathname;
            }
        }

        ksort($files);
        // END

        return new ArrayIterator(array_values($files));
    }

    /**
     * Creates a {@link PHP_Depend_Metrics_AnalyzerLoader} instance that will be
     * used to create all analyzers required for the actually registered logger
     * instances.
     *
     * @param array $options The command line options recieved for this run.
     *
     * @return PHP_Depend_Metrics_AnalyzerLoader
     */
    private function _createAnalyzerLoader(array $options)
    {
        $analyzerSet = array();

        foreach ($this->_loggers as $logger) {
            foreach ($logger->getAcceptedAnalyzers() as $type) {
                // Check for type existence
                if (in_array($type, $analyzerSet) === false) {
                    $analyzerSet[] = $type;
                }
            }
        }

        $cacheKey = md5(serialize($this->_files) . serialize($this->_directories));

        $loader = new PHP_Depend_Metrics_AnalyzerLoader(
            new PHP_Depend_Metrics_AnalyzerClassFileSystemLocator(),
            $this->_cacheFactory->create($cacheKey),
            $analyzerSet,
            $options
        );

        return $this->_initAnalyseListeners($loader);
    }
}

class PHP_Depend_Log_Processor extends PHP_Depend_Util_Processor
{
    public function register(PHP_Depend_Log_Logger $logger)
    {
        $this->registerVisitor($logger);
    }
}