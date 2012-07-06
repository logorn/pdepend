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

namespace PHP\Depend\AST;

use \PHP_Depend_AST_Type;
use \PHPParser_Node_Stmt_Interface;

/**
 * AST node that represents a PHP interface.
 *
 * @category  QualityAssurance
 * @author    Manuel Pichler <mapi@pdepend.org>
 * @copyright 2008-2012 Manuel Pichler. All rights reserved.
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version   Release: @package_version@
 * @link      http://pdepend.org/
 * @since     2.0.0
 */
class ASTInterface extends PHPParser_Node_Stmt_Interface implements PHP_Depend_AST_Type
{
    /**
     * References to other ast nodes.
     *
     * @var \PHP\Depend\AST\ASTInterfaceRefs
     */
    private $refs;

    /**
     * Constructs a new interface instance.
     *
     * @param \PHPParser_Node_Stmt_Interface $interface
     * @param \PHP\Depend\AST\ASTInterfaceRefs $refs
     */
    public function __construct(PHPParser_Node_Stmt_Interface $interface, ASTInterfaceRefs $refs)
    {
        parent::__construct(
            $interface->name,
            array(
                'extends'  => $interface->extends,
                'stmts'    => $interface->stmts
            ),
            array_merge(array('user_defined' => true), $interface->attributes)
        );

        $this->refs           = $refs;
        $this->namespacedName = $interface->namespacedName;

        $this->refs->initialize($this);
    }

    /**
     * Returns the global identifier for this node.
     *
     * @return string
     */
    public function getId()
    {
        return $this->getAttribute('id');
    }

    /**
     * Returns the name for this type.
     *
     * @return string
     */
    public function getName()
    {
        return (string) $this->namespacedName;
    }

    /**
     * Returns the namespace for this node.
     *
     * @return \PHP_Depend_AST_Namespace
     */
    public function getNamespace()
    {
        return $this->refs->getNamespace();
    }

    /**
     * Returns an array with all parent interfaces.
     *
     * @return \PHP\Depend\AST\ASTInterface[]
     */
    public function getInterfaces()
    {
        return $this->refs->getParentInterfaces();
    }

    /**
     * Returns all methods declared by this interface.
     *
     * @return \PHP\Depend\AST\ASTMethod[]
     */
    public function getMethods()
    {
        $methods = array();
        foreach ($this->stmts as $stmt) {

            if ($stmt instanceof ASTMethod) {

                $methods[] = $stmt;
            }
        }
        return $methods;
    }

    /**
     * Returns <b>true</b> when this node was parsed from a source file.
     *
     * @return boolean
     */
    public function isUserDefined()
    {
        return $this->attributes['user_defined'];
    }

    /**
     * Checks if this type is a subtype of the given <b>$type</b>.
     *
     * @param \PHP_Depend_AST_Type $type
     *
     * @return boolean
     */
    public function isSubtypeOf(PHP_Depend_AST_Type $type)
    {
        if ($type->namespacedName === $this->namespacedName) {
            return true;
        }

        foreach ($this->refs->getParentInterfaces() as $interface) {

            if ($type->isSubtypeOf($interface)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Magic wake up method that will register this object in the global node
     * reference context.
     *
     * @return void
     * @access private
     */
    public function __wakeup()
    {
        $this->refs->initialize($this);
    }
}