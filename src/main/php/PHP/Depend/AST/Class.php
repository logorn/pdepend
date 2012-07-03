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
 * @subpackage AST
 * @author     Manuel Pichler <mapi@pdepend.org>
 * @copyright  2008-2012 Manuel Pichler. All rights reserved.
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    SVN: $Id$
 * @link       http://pdepend.org/
 */

use \PHP\Depend\AST\Properties;

/**
 * Custom AST node that represents a PHP class.
 *
 * @category   QualityAssurance
 * @package    PHP_Depend
 * @subpackage AST
 * @author     Manuel Pichler <mapi@pdepend.org>
 * @copyright  2008-2012 Manuel Pichler. All rights reserved.
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    Release: @package_version@
 * @link       http://pdepend.org/
 * @since      2.0.0
 */
class PHP_Depend_AST_Class extends PHPParser_Node_Stmt_Class implements PHP_Depend_AST_Type
{
    /**
     * @var PHP_Depend_AST_ClassRefs
     */
    private $refs;

    /**
     * Constructs a new class instance.
     *
     * @param PHPParser_Node_Stmt_Class $class
     * @param PHP_Depend_AST_ClassRefs  $refs
     */
    public function __construct(PHPParser_Node_Stmt_Class $class, PHP_Depend_AST_ClassRefs $refs)
    {
        parent::__construct(
            $class->name,
            array(
                'type'       => $class->type,
                'extends'    => $class->extends,
                'implements' => $class->implements,
                'stmts'      => $class->stmts,
            ),
            array_merge(array('user_defined' => true), $class->attributes)
        );

        $this->refs           = $refs;
        $this->namespacedName = $class->namespacedName;

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
     * Returns the namespace where this method is declared.
     *
     * @return PHP_Depend_AST_Namespace
     */
    public function getNamespace()
    {
        return $this->refs->getNamespace();
    }

    /**
     * Returns the parent class of this class or <b>NULL</b> when this class
     * has no parent.
     *
     * @return null|PHP_Depend_AST_Class
     */
    public function getParentClass()
    {
        return $this->refs->getParentClass();
    }

    /**
     * Returns all interfaces implemented by this class.
     *
     * @return PHP_Depend_AST_Interface[]
     */
    public function getInterfaces()
    {
        return $this->refs->getImplementedInterfaces();
    }

    /**
     * Returns all properties declared by this class.
     *
     * @return \PHP\Depend\AST\Properties[]
     */
    public function getProperties()
    {
        $properties = array();
        foreach ($this->stmts as $stmt) {

            if ($stmt instanceof Properties) {

                $properties[] = $stmt;
            }
        }
        return $properties;
    }

    /**
     * Returns all methods declared by this class.
     *
     * @return PHP_Depend_AST_Method[]
     */
    public function getMethods()
    {
        $methods = array();
        foreach ($this->stmts as $stmt) {

            if ($stmt instanceof PHP_Depend_AST_Method) {

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
     * Returns <b>true</b> when this class was declared as abstract.
     *
     * @return boolean
     */
    public function isAbstract()
    {
        return ($this->type & self::MODIFIER_ABSTRACT);
    }

    /**
     * Checks if this type is a subtype of the given <b>$type</b>.
     *
     * @param PHP_Depend_AST_Type $type
     *
     * @return boolean
     */
    public function isSubtypeOf(PHP_Depend_AST_Type $type)
    {
        if ($type->getId() === $this->getId()) {
            return true;
        }

        if ($this->extends && $type->isSubtypeOf($this->getParentClass())) {
            return true;
        }

        foreach ($this->refs->getImplementedInterfaces() as $interface) {

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