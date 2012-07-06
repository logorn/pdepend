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

use \PHPParser_Node_Stmt_Property;

/**
 * Custom AST node that represents a PHP properties collection.
 *
 * <code>
 * class Foo {
 *     // Collection one
 *     private $foo,
 *             $bar,
 *             $baz;
 *
 *     // Collection two
 *     private $foobar;
 * }
 * </code>
 *
 * @category  QualityAssurance
 * @author    Manuel Pichler <mapi@pdepend.org>
 * @copyright 2008-2012 Manuel Pichler. All rights reserved.
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version   Release: @package_version@
 * @link      http://pdepend.org/
 * @since     2.0.0
 */
class ASTProperties extends PHPParser_Node_Stmt_Property implements ASTNode
{
    /**
     * Constructs a new properties collection.
     *
     * @param PHPParser_Node_Stmt_Property $property
     */
    public function __construct(PHPParser_Node_Stmt_Property $property)
    {
        parent::__construct(
            $property->type,
            $property->props,
            $property->attributes
        );
    }

    /**
     * Returns all properties available in this collection.
     *
     * @return \PHP\Depend\AST\Property[]
     */
    public function getProperties()
    {
        return $this->props;
    }

    /**
     * Returns <b>true</b> when this properties collection is declared public.
     *
     * @return boolean
     */
    public function isPublic()
    {
        return (boolean) ($this->type & ASTClass::MODIFIER_PUBLIC);
    }

    /**
     * Returns <b>true</b> when this properties collection is declared private.
     *
     * @return boolean
     */
    public function isPrivate()
    {
        return (boolean) ($this->type & ASTClass::MODIFIER_PRIVATE);
    }

    /**
     * Returns the global identifier for this node.
     *
     * @return string
     */
    public function getId()
    {
        return join('-', array_map(
            function(ASTProperty $property) {
                return $property->getId();
            },
            $this->props
        ));
    }

    /**
     * Returns the name for this node.
     *
     * @return string
     */
    public function getName()
    {
        return join('-', array_map(
            function(ASTProperty $property) {
                return $property->getName();
            },
            $this->props
        ));
    }


}