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
 * @subpackage Parser
 * @author     Manuel Pichler <mapi@pdepend.org>
 * @copyright  2008-2012 Manuel Pichler. All rights reserved.
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    SVN: $Id$
 * @link       http://pdepend.org/
 */

/**
 * Visitor class that generates unique node identifiers for classes, namespaces,
 * interfaces and functions/methods.
 *
 * @category   QualityAssurance
 * @package    PHP_Depend
 * @subpackage Parser
 * @author     Manuel Pichler <mapi@pdepend.org>
 * @copyright  2008-2012 Manuel Pichler. All rights reserved.
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    Release: @package_version@
 * @link       http://pdepend.org/
 * @since      2.0.0
 */
class PHP_Depend_Parser_IdGenerator extends PHPParser_NodeVisitorAbstract
{
    /**
     * Fragments the build of the node identifier.
     *
     * @var array
     */
    private $parts = array();

    /**
     * Extracts the name of several node types and adds them to the internally
     * used node identifier tree.
     *
     * @param PHPParser_Node $node
     *
     * @return null
     */
    public function enterNode(PHPParser_Node $node)
    {
        if ($node instanceof PHPParser_Node_Stmt_Class) {

            array_push($this->parts, "\\{$node->name}");
        } else if ($node instanceof PHPParser_Node_Stmt_Interface) {

            array_push($this->parts, "\\{$node->name}");
        } else if ($node instanceof PHPParser_Node_Stmt_Namespace) {

            array_push($this->parts, "\\{$node->name}");
        } else if ($node instanceof PHPParser_Node_Stmt_PropertyProperty) {

            array_push($this->parts, "::\${$node->name}");
        } else if ($node instanceof PHPParser_Node_Stmt_ClassMethod) {

            array_push($this->parts, "::{$node->name}()");
        } else if ($node instanceof PHPParser_Node_Stmt_Function) {

            array_push($this->parts, "\\{$node->name}()");
        }
    }

    /**
     * Sets a unique identifier on the given node. The ID will be stored in a
     * node attribute named <b>"id"</b>.
     *
     * @param PHPParser_Node $node Node
     *
     * @return null
     */
    public function leaveNode(PHPParser_Node $node)
    {
        if ($node instanceof PHPParser_Node_Stmt_Class) {

            $id = join('', $this->parts) . '#c';
            array_pop($this->parts);
        } else if ($node instanceof PHPParser_Node_Stmt_Interface) {

            $id = join('', $this->parts) . '#i';
            array_pop($this->parts);
        } else if ($node instanceof PHPParser_Node_Stmt_Namespace) {

            $id = join('', $this->parts) . '#n';
            array_pop($this->parts);
        } else if ($node instanceof PHPParser_Node_Stmt_PropertyProperty) {

            $id = join('', $this->parts) . '#p';
            array_pop($this->parts);
        } else if ($node instanceof PHPParser_Node_Stmt_ClassMethod) {

            $id = join('', $this->parts) . '#m';
            array_pop($this->parts);
        } else if ($node instanceof PHPParser_Node_Stmt_Function) {

            $id = join('', $this->parts) . '#f';
            array_pop($this->parts);
        }

        if (isset($id)) {
            $node->setAttribute(
                'id',
                ltrim(
                    preg_replace(
                        '([^\w\d:\(\)\.\$\\\\#]+)i',
                        '',
                        $id
                    ),
                    '\\'
                )
            );
        }
    }
}