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
 * @version   Release: @package_version@
 * @link      http://pdepend.org/
 */

namespace PHP\Depend\AST;

use \PHP\Depend\Context;

/**
 * Container class that holds nodes referenced by a property.
 *
 * @category  QualityAssurance
 * @author    Manuel Pichler <mapi@pdepend.org>
 * @copyright 2008-2012 Manuel Pichler. All rights reserved.
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version   Release: @package_version@
 * @link      http://pdepend.org/
 * @since     2.0.0
 */
class ASTPropertyRefs
{
    /**
     * @var \PHP\Depend\Context
     */
    private $context;

    /**
     * @var string
     */
    private $namespace;

    /**
     * @var string
     */
    private $declaringType;

    /**
     * @var string
     */
    private $type;

    /**
     * Constructs a new reference context for an object property.
     *
     * @param \PHP\Depend\Context $context
     * @param string $namespace
     * @param string $declaringType
     * @param string $type
     */
    public function __construct(
        Context $context,
        $namespace,
        $declaringType,
        $type = null
    )
    {
        $this->type          = $type ? $type : null;
        $this->context       = $context;
        $this->namespace     = $namespace;
        $this->declaringType = $declaringType;
    }

    /**
     * Returns the namespace for the context interface.
     *
     * @return \PHP\Depend\AST\ASTNamespace
     */
    public function getNamespace()
    {
        return $this->context->getNamespace($this->namespace);
    }

    /**
     * Returns the declaring type for the context method.
     *
     * @return \PHP\Depend\AST\ASTType
     */
    public function getDeclaringType()
    {
        return $this->context->getType($this->declaringType);
    }

    /**
     * @return null|\PHP\Depend\AST\ASTType
     */
    public function getType()
    {
        if ($this->type) {
            return $this->context->getType($this->type);
        }
        return null;
    }

    /**
     * Initializes this reference instance for the given property.
     *
     * @param \PHP\Depend\AST\ASTProperty $property
     *
     * @return void
     */
    public function initialize(ASTProperty $property)
    {
        $this->context->registerNode($property);
    }
}
