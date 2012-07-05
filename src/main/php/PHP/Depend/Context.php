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
 * Context is used at runtime to establish inter node dependencies.
 *
 * @category  QualityAssurance
 * @package   PHP_Depend
 * @author    Manuel Pichler <mapi@pdepend.org>
 * @copyright 2008-2012 Manuel Pichler. All rights reserved.
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version   Release: @package_version@
 * @link      http://pdepend.org/
 * @since     2.0.0
 */
class PHP_Depend_Context
{
    /**
     * All registered nodes.
     *
     * @var PHP_Depend_AST_Node[]
     */
    private $nodes = array();

    /**
     * Registers the given node in the global context.
     *
     * @param PHP_Depend_AST_Node $node
     *
     * @return void
     */
    public function registerNode(PHP_Depend_AST_Node $node)
    {
        $this->nodes[$node->getId()] = $node;
    }

    /**
     * Returns a namespace for the given <b>$id</b> or a dummy namespace when no
     * matching namespace exists.
     *
     * @param string $id
     *
     * @return PHP_Depend_AST_Namespace
     */
    public function getNamespace($id)
    {
        if ($namespace = $this->getNode("{$id}#n")) {
            return $namespace;
        }

        return new PHP_Depend_AST_Namespace(
            new PHPParser_Node_Stmt_Namespace(
                new PHPParser_Node_Name($id ? $id : '+global'),
                array(),
                array(
                    'user_defined' => false,
                    'id' => ($id ? $id : '+global') . '#n'
                )
            ),
            new PHP_Depend_AST_NamespaceRefs($this)
        );
    }

    /**
     * Returns a class for the given <b>$id</b> or <b>NULL</b> when no
     * matching class exists.
     *
     * @param string $id
     *
     * @return PHP_Depend_AST_Class
     */
    public function getClass($id)
    {
        if ($class = $this->getNode("{$id}#c")) {
            return $class;
        }

        if ($id) {

            // TODO 2.0 extract name/namespace from id.
            return new PHP_Depend_AST_Class(
                new PHPParser_Node_Stmt_Class(
                    $id,
                    array('namespacedName' => $id),
                    array('user_defined' => false, 'id' => "{$id}#c")
                ),
                new PHP_Depend_AST_ClassRefs(
                    $this, '+global', null, array()
                )
            );
        }
    }

    /**
     * Returns an interface for the given <b>$id</b> or <b>NULL</b> when no
     * matching interface exists.
     *
     * @param string $id
     *
     * @return null|PHP_Depend_AST_Interface
     */
    public function getInterface($id)
    {
        if ($interface = $this->getNode("{$id}#i")) {

            return $interface;
        }

        if ($id) {

            // TODO 2.0 extract name/namespace from id.
            return new PHP_Depend_AST_Interface(
                new PHPParser_Node_Stmt_Interface(
                    $id,
                    array('namespacedName' => $id),
                    array('user_defined' => false, 'id' => "{$id}#i")
                ),
                new PHP_Depend_AST_InterfaceRefs(
                    $this, '+global', array()
                )
            );
        }
    }

    /**
     * Returns a type for the given <b>$id</b> or <b>NULL</b> when no
     * matching type exists.
     *
     * @param string $id
     *
     * @return PHP_Depend_AST_Type
     * @todo Implement traits
     */
    public function getType($id)
    {
        if ($type = $this->getNode("{$id}#i")) {
            return $type;
        } else if ($type = $this->getNode("{$id}#c")) {
            return $type;
        }
        return $this->getClass($id);
    }

    /**
     * Returns a method for the given <b>$id</b> or <b>NULL</b> when no
     * matching method exists.
     *
     * @param string $id
     *
     * @return null|PHP_Depend_AST_Method
     */
    public function getMethod($id)
    {
        return $this->getNode("{$id}#m");
    }

    /**
     * Returns a function for the given <b>$id</b> or <b>NULL</b> when no
     * matching function exists.
     *
     * @param string $id
     *
     * @return null|PHP_Depend_AST_Function
     */
    public function getFunction($id)
    {
        return $this->getNode("{$id}#f");
    }

    /**
     * Returns a node for the given <b>$id</b> or <b>NULL</b> when no
     * matching node exists.
     *
     * @param string $id
     *
     * @return null|PHP_Depend_AST_Node
     */
    private function getNode($id)
    {
        if (isset($this->nodes[$id])) {
            return $this->nodes[$id];
        }
        return null;
    }
}