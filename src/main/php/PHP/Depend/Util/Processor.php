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

namespace PHP\Depend\Util;

use \PHPParser_Node;
use \PHPParser_NodeTraverser;
use \PHPParser_NodeVisitor;

/**
 * Generic compilation unit processor that traverses a node tree.
 *
 * @category  QualityAssurance
 * @author    Manuel Pichler <mapi@pdepend.org>
 * @copyright 2008-2012 Manuel Pichler. All rights reserved.
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version   Release: @package_version@
 * @link      http://pdepend.org/
 * @since     2.0.0
 */
abstract class Processor extends PHPParser_NodeTraverser implements PHPParser_NodeVisitor
{
    private $regexp = '(^(visit[\w\d]+(Before|After)|(before|after)Traverse)$)';

    /**
     * Array with all registered visitors
     *
     * @var stdClass[]
     */
    private $nodeVisitors = array();

    /**
     * Temporary data container that holds processing data for the registered
     * visitors.
     *
     * @var mixed[]
     */
    private $data = array();

    /**
     * Available node callbacks in the registered visitors.
     *
     * @var array
     */
    private $callbacks = array(
        'beforeTraverse' => array(),
        'afterTraverse' => array()
    );

    /**
     * Constructs a new ast processor.
     */
    public function __construct()
    {
        $this->addVisitor($this);
    }

    /**
     * Registers the given visitor for the next AST processing.
     *
     * @param stdClass $visitor
     *
     * @return void
     */
    protected function registerVisitor($visitor)
    {
        $class = get_class($visitor);

        foreach (get_class_methods($visitor) as $method) {

            if (0 === preg_match($this->regexp, $method)) {
                continue;
            }

            if (false === isset($this->callbacks[$method])) {

                $this->callbacks[$method] = array();
            }

            $this->callbacks[$method][] = $class;
        }

        $this->data[$class]         = null;
        $this->nodeVisitors[$class] = $visitor;
    }

    /**
     * Returns all registered visitor objects.
     *
     * @return stdClass[]
     */
    public function getVisitors()
    {
        return $this->nodeVisitors;
    }

    /**
     * Processes the given compilation units with all registered analyzers.
     *
     * @param \PHP\Depend\AST\ASTCompilationUnit[] $compilationUnit
     *
     * @return void
     */
    public function process(array $compilationUnit)
    {
        $this->traverse($compilationUnit);
    }

    /**
     * Called once before traversal.
     *
     * Return value semantics:
     *  * null:      $nodes stays as-is
     *  * otherwise: $nodes is set to the return value
     *
     * @param PHPParser_Node[] $nodes Array of nodes
     *
     * @return null|PHPParser_Node[] Array of nodes
     */
    public function beforeTraverse(array $nodes)
    {
        foreach (array_keys($this->data) as $class) {

            $this->data[$class] = null;
        }

        foreach ($this->callbacks[__FUNCTION__] as $class) {

            $this->nodeVisitors[$class]->beforeTraverse($nodes);
        }
    }

    /**
     * Called when entering a node.
     *
     * Return value semantics:
     *  * null:      $node stays as-is
     *  * otherwise: $node is set to the return value
     *
     * @param PHPParser_Node $node Node
     *
     * @return null|PHPParser_Node Node
     */
    public function enterNode(PHPParser_Node $node)
    {
        $this->invoke($node, 'Before');
    }

    /**
     * Called when leaving a node.
     *
     * Return value semantics:
     *  * null:      $node stays as-is
     *  * false:     $node is removed from the parent array
     *  * array:     The return value is merged into the parent array (at the
     *               position of the $node)
     *  * otherwise: $node is set to the return value
     *
     * @param PHPParser_Node $node Node
     *
     * @return null|PHPParser_Node|false|PHPParser_Node[] Node
     */
    public function leaveNode(PHPParser_Node $node)
    {
        $this->invoke($node, 'After');
    }

    /**
     * Called once after traversal.
     *
     * Return value semantics:
     *  * null:      $nodes stays as-is
     *  * otherwise: $nodes is set to the return value
     *
     * @param PHPParser_Node[] $nodes Array of nodes
     *
     * @return null|PHPParser_Node[] Array of nodes
     */
    public function afterTraverse(array $nodes)
    {
        foreach (array_keys($this->data) as $class) {

            $this->data[$class] = null;
        }

        foreach ($this->callbacks[__FUNCTION__] as $class) {

            $this->nodeVisitors[$class]->afterTraverse($nodes);
        }
    }

    /**
     * Invokes the <b>visit{XXX}Before()</b> or <b>visit{XXX}After()</b>,
     * depending on the given <b>$eventType</b> parameter, method on the
     * registered visitors.
     *
     * @param PHPParser_Node $node
     * @param string         $eventType
     *
     * @return void
     */
    private function invoke(PHPParser_Node $node, $eventType)
    {
        $callback = sprintf(
            'visit%s%s',
            str_replace('_', '', substr(get_class($node), 15)),
            $eventType
        );

        if (false === isset($this->callbacks[$callback])) {
            return;
        }

        foreach ($this->callbacks[$callback] as $class) {

            $this->data[$class] = $this->nodeVisitors[$class]->$callback(
                $node,
                $this->data[$class]
            );
        }
    }
}

