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

use \PHPParser_Node_Stmt_PropertyProperty;

/**
 * Custom AST node that represents a PHP property.
 *
 * @category  QualityAssurance
 * @author    Manuel Pichler <mapi@pdepend.org>
 * @copyright 2008-2012 Manuel Pichler. All rights reserved.
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version   Release: @package_version@
 * @link      http://pdepend.org/
 * @since     2.0.0
 *
 * @property \PHPParser_Node_Name $type
 */
class ASTProperty extends PHPParser_Node_Stmt_PropertyProperty implements ASTNode
{
    /**
     * Will be true when this object was restored from cache.
     *
     * @var boolean
     */
    public $cached = false;

    /**
     * Reference context used to retrieve referenced nodes.
     *
     * @var \PHP\Depend\AST\ASTPropertyRefs
     */
    private $refs;

    /**
     * Constructs a new property AST node.
     *
     * @param \PHPParser_Node_Stmt_PropertyProperty $property
     * @param \PHP\Depend\AST\ASTPropertyRefs $refs
     * @param integer $modifier
     */
    public function __construct(
        PHPParser_Node_Stmt_PropertyProperty $property,
        ASTPropertyRefs $refs,
        $modifier
    )
    {
        parent::__construct(
            $property->name,
            $property->default,
            $property->attributes
        );

        $this->refs           = $refs;
        $this->type           = $property->type;
        $this->modifier       = $modifier;
        $this->namespacedName = $property->namespacedName;

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
     * Returns the name for this node.
     *
     * @return string
     */
    public function getName()
    {
        return (string) $this->name;
    }

    /**
     * Returns the namespace where this property is declared.
     *
     * @return \PHP\Depend\AST\ASTNamespace
     */
    public function getNamespace()
    {
        return $this->refs->getNamespace();
    }

    /**
     * Returns the declaring type for this property.
     *
     * @return \PHP_Depend_AST_Type
     */
    public function getDeclaringType()
    {
        return $this->refs->getDeclaringType();
    }

    /**
     * Returns the type of this property or <b>NULL</b> when this property does
     * not reference another none scalar type.
     *
     * @return \PHP_Depend_AST_Type
     */
    public function getType()
    {
        return $this->refs->getType();
    }

    /**
     * Returns <b>true</b> when this properties collection is declared public.
     *
     * @return boolean
     */
    public function isPublic()
    {
        return (boolean) ($this->modifier & ASTClass::MODIFIER_PUBLIC);
    }

    /**
     * Returns <b>true</b> when this properties collection is declared private.
     *
     * @return boolean
     */
    public function isPrivate()
    {
        return (boolean) ($this->modifier & ASTClass::MODIFIER_PRIVATE);
    }

    /**
     * Returns <b>true</b> when this property instance was restored from cache,
     * otherwise this method will return <b>false</b>.
     *
     * @return boolean
     */
    public function isCached()
    {
        return $this->cached;
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
        $this->cached = true;

        $this->refs->initialize($this);
    }
}