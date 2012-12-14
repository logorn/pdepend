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
 * @author    Manuel Pichler <mapi@pdepend.org>
 * @copyright 2008-2012 Manuel Pichler. All rights reserved.
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version   SVN: $Id$
 * @link      http://pdepend.org/
 */

namespace PHP\Depend\Metrics;

use \PHP\Depend\Util\Cache\CacheDriver;

/**
 * This class provides a simple way to load all required analyzers by class,
 * implemented interface or parent class.
 *
 * @category  QualityAssurance
 * @author    Manuel Pichler <mapi@pdepend.org>
 * @copyright 2008-2012 Manuel Pichler. All rights reserved.
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version   Release: @package_version@
 * @link      http://pdepend.org/
 */
class AnalyzerLoader
{
    /**
     * @var boolean
     */
    private $initialized = false;

    /**
     * Stack of loaded analyzers.
     *
     * @var \PHP\Depend\Metrics\Analyzer[][]
     */
    private $steps = array();

    private $acceptedTypes;

    private $options;

    /**
     * The system wide used cache.
     *
     * @var \PHP\Depend\Util\Cache\CacheDriver
     * @since 1.0.0
     */
    private $cache;

    /**
     * Used locator for installed analyzer classes.
     *
     * @var \PHP_Depend_Metrics_AnalyzerClassLocator
     */
    private $classLocator;

    /**
     * Constructs a new analyzer loader.
     *
     * @param \PHP_Depend_Metrics_AnalyzerClassLocator $classLocator
     * @param \PHP\Depend\Util\Cache\CacheDriver $cache
     * @param string[] $acceptedTypes
     * @param array $options
     */
    public function __construct(
        \PHP_Depend_Metrics_AnalyzerClassLocator $classLocator,
        CacheDriver $cache,
        array $acceptedTypes,
        array $options = array()
    )
    {
        $this->cache        = $cache;
        $this->classLocator = $classLocator;

        $this->options       = $options;
        $this->acceptedTypes = $acceptedTypes;
    }

    /**
     * Returns an array of {@link \PHP\Depend\Metrics\Analyzer} objects
     * that match against the configured analyzer types.
     *
     * @return \PHP\Depend\Metrics\Analyzer[][]
     */
    public function getAnalyzers()
    {
        if (false === $this->initialized) {

            $this->initialize();
            $this->initialized = true;
        }
        return $this->steps;
    }

    /**
     * Initializes all accepted analyzers.
     *
     * @return void
     * @since 0.9.10
     */
    private function initialize()
    {
        $this->loadByType($this->acceptedTypes);

        foreach ($this->steps as $step => $analyzers) {

            $this->steps[$step] = $this->filter($analyzers);
        }
        $this->steps = array_filter($this->steps);
    }

    /**
     * Filters all analyzers that are not enabled.
     *
     * @param \PHP\Depend\Metrics\Analyzer[] $analyzers
     *
     * @return \PHP\Depend\Metrics\Analyzer[]
     */
    private function filter(array $analyzers)
    {
        foreach ($analyzers as $name => $analyzer) {

            if ($analyzer->isEnabled()) {
                continue;
            }
            unset($analyzers[$name]);
        }

        return $analyzers;
    }

    /**
     * Loads all accepted node analyzers.
     *
     * @param array $types Accepted/expected analyzer types.
     *
     * @return \PHP\Depend\Metrics\Analyzer[]
     */
    private function loadByType(array $types)
    {
        $analyzers = array();
        foreach ($this->classLocator->findAll() as $reflection) {

            if ($this->isInstanceOf($reflection, $types)) {

                $analyzers[] = $this->createOrReturnAnalyzer($reflection);
            }
        }
        return $analyzers;
    }

    /**
     * This method checks if the given analyzer class implements one of the
     * expected analyzer types.
     *
     * @param \ReflectionClass $reflection
     * @param array $types
     *
     * @return boolean
     * @since 0.9.10
     */
    private function isInstanceOf(\ReflectionClass $reflection, array $types)
    {
        foreach ($types as $type) {

            if (interface_exists($type) && $reflection->implementsInterface($type)) {
                return true;
            }

            if (class_exists($type) && $reflection->isSubclassOf($type)) {
                return true;
            }

            if (strcasecmp($reflection->getName(), $type) === 0) {
                return true;
            }
        }
        return false;
    }

    /**
     * This method creates a new analyzer instance or returns a previously
     * created instance of the given reflection instance.
     *
     * @param \ReflectionClass $reflection Reflection class for an analyzer.
     *
     * @return \PHP\Depend\Metrics\Analyzer
     * @since 0.9.10
     */
    private function createOrReturnAnalyzer(\ReflectionClass $reflection)
    {
        $name = $reflection->getName();
        foreach ($this->steps as $step) {

            if (isset($step[$name])) {

                return $step[$name];
            }
        }
        return $this->createAndConfigure($reflection);
    }

    /**
     * Creates an analyzer instance of the given reflection class instance.
     *
     * @param \ReflectionClass $reflection Reflection class for an analyzer.
     *
     * @return \PHP\Depend\Metrics\Analyzer
     * @since 0.9.10
     */
    private function createAndConfigure(\ReflectionClass $reflection)
    {
        if ($reflection->getConstructor()) {

            $analyzer = $reflection->newInstance($this->options);
        } else {

            $analyzer = $reflection->newInstance();
        }

        return $this->configure($analyzer);
    }

    /**
     * Initializes the given analyzer instance.
     *
     * @param \PHP\Depend\Metrics\Analyzer $analyzer
     *
     * @return \PHP\Depend\Metrics\Analyzer
     * @since 0.9.10
     */
    private function configure(Analyzer $analyzer)
    {
        if ($analyzer instanceof CacheAware) {
            $analyzer->setCache($this->cache);
        }

        if (!($analyzer instanceof AggregateAnalyzer)) {

            $this->steps[0][get_class($analyzer)] = $analyzer;

            return $analyzer;
        }

        $required = $this->loadByType($analyzer->getRequiredAnalyzers());
        foreach ($required as $requiredAnalyzer) {

            $analyzer->addAnalyzer($requiredAnalyzer);
        }

        $index = 0;
        foreach ($required as $requiredAnalyzer) {

            foreach ($this->steps as $i => $step) {

                if (isset($step[get_class($requiredAnalyzer)])) {

                    $index = max($i, $index);
                }
            }
        }

        $this->steps[$index + 1][get_class($analyzer)] = $analyzer;

        return $analyzer;
    }
}
