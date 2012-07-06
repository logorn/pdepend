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

/**
 * Proxy ast node that represents a concrete type with in the AST.
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
 *
 * @property PHPParser_Node_Name $namespacedName
 */
class PHP_Depend_AST_TypeRef extends PHPParser_NodeAbstract implements PHP_Depend_AST_Type
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var PHP_Depend_Context
     */
    private $context;

    /**
     * Constructs a new type reference.
     *
     * @param PHP_Depend_Context $context
     * @param string             $name
     */
    public function __construct(PHP_Depend_Context $context, $name)
    {
        parent::__construct(
            array('namespacedName'  => new PHPParser_Node_Name($name))
        );

        $this->name    = $name;
        $this->context = $context;
    }

    /**
     * Returns the global identifier for this node.
     *
     * @return string
     */
    public function getId()
    {
        return $this->getType()->getId();
    }

    /**
     * Returns the name for this type.
     *
     * @return string
     */
    public function getName()
    {
        return $this->getType()->getName();
    }

    /**
     * Returns all methods declared by this type.
     *
     * @return \PHP\Depend\AST\ASTMethod[]
     */
    public function getMethods()
    {
        return $this->getType()->getMethods();
    }

    /**
     * Returns the namespace where this method is declared.
     *
     * @return \PHP\Depend\AST\ASTNamespace
     */
    public function getNamespace()
    {
        return $this->getType()->getNamespace();
    }

    /**
     * Returns <b>true</b> when this node was parsed from a source file.
     *
     * @return boolean
     */
    public function isUserDefined()
    {
        return $this->getType()->isUserDefined();
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
        return $this->getType()->isSubtypeOf($type);
    }

    /**
     * Returns the original type referenced by this object.
     *
     * @return \PHP_Depend_AST_Type
     */
    public function getType()
    {
        return $this->context->getType($this->name);
    }
}