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

namespace PHP\Depend\Log\Summary;

use \PHP\Depend\Metrics\Listener;
use \PHP\Depend\Metrics\NodeAware;

/**
 * Dummy implementation of an analyzer.
 *
 * @category  QualityAssurance
 * @author    Manuel Pichler <mapi@pdepend.org>
 * @copyright 2008-2012 Manuel Pichler. All rights reserved.
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version   Release: @package_version@
 * @link      http://pdepend.org/
 */
class AnalyzerNodeAwareDummy implements NodeAware
{
    /**
     * Dummy node metrics.
     *
     * @var array(string=>array) $nodeMetrics
     */
    protected $nodeMetrics = null;

    /**
     * Constructs a new analyzer dummy instance.
     *
     * @param array(string=>array) $nodeMetrics Dummy node metrics.
     */
    public function __construct(array $nodeMetrics = array())
    {
        $this->nodeMetrics = $nodeMetrics;
    }

    /**
     * Adds a listener to this analyzer.
     *
     * @param \PHP\Depend\Metrics\Listener $listener
     *
     * @return void
     */
    public function addAnalyzeListener(Listener $listener)
    {
    }

    /**
     * Removes the listener from this analyzer.
     *
     * @param \PHP\Depend\Metrics\Listener $listener
     *
     * @return void
     */
    public function removeAnalyzeListener(Listener $listener)
    {
    }

    /**
     * By default all analyzers are enabled. Overwrite this method to provide
     * state based disabling/enabling.
     *
     * @return boolean
     * @since 0.9.10
     */
    public function isEnabled()
    {
        return true;
    }

    /**
     * Returns an array with metrics for the requested node.
     *
     * @param \PHP\Depend\AST\ASTNode|string $node
     *
     * @return array
     */
    public function getNodeMetrics($node)
    {
        $nodeId = (string)is_object($node) ? $node->getId() : $node;

        if (isset($this->nodeMetrics[$nodeId])) {
            return $this->nodeMetrics[$nodeId];
        }
        return array();
    }

}
