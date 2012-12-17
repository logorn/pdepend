<?php
/**
 * This file is part of PDepend.
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

use \PHP\Depend\Context;

/**
 * Container holding nodes referenced by a class.
 *
 * @category  QualityAssurance
 * @author    Manuel Pichler <mapi@pdepend.org>
 * @copyright 2008-2012 Manuel Pichler. All rights reserved.
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version   Release: @package_version@
 * @link      http://pdepend.org/
 * @since     2.0.0
 */
class ASTClassRefs
{
    /**
     * @var \PHP\Depend\Context
     */
    private $context;

    /**
     * @var string
     */
    private $namespaceId;

    /**
     * @var string
     */
    private $parentClassId;

    /**
     * @var string[]
     */
    private $implementedInterfaceIds;

    /**
     * asdasd
     *
     * @param \PHP\Depend\Context $context
     * @param string $namespaceId
     * @param string $parentClassId
     * @param string[] $implementedInterfaceIds
     */
    public function __construct(
        Context $context,
        $namespaceId,
        $parentClassId,
        array $implementedInterfaceIds
    )
    {
        $this->context                 = $context;
        $this->namespaceId             = $namespaceId;
        $this->parentClassId           = $parentClassId;
        $this->implementedInterfaceIds = $implementedInterfaceIds;
    }

    /**
     * Returns the parent namespace of the context class.
     *
     * @return \PHP\Depend\AST\ASTNamespace
     */
    public function getNamespace()
    {
        return $this->context->getNamespace($this->namespaceId);
    }

    /**
     * Returns the parent class or <b>NULL</b> if the parent class does not
     * declare a parent.
     *
     * @return null|\PHP\Depend\AST\ASTClass
     */
    public function getParentClass()
    {
        if (null === $this->parentClassId) {
            return null;
        }
        return $this->context->getClass($this->parentClassId);
    }

    /**
     * Returns all interfaces that are implemented by the context class.
     *
     * @return \PHP\Depend\AST\ASTInterface[]
     */
    public function getImplementedInterfaces()
    {
        $implemented = array();
        foreach ($this->implementedInterfaceIds as $interfaceId) {

            $implemented[] = $this->context->getInterface($interfaceId);
        }
        return $implemented;
    }

    /**
     * Registers the given class in the shared code context.
     *
     * @param \PHP\Depend\AST\ASTClass $class
     * @return void
     */
    public function initialize(ASTClass $class)
    {
        $this->context->registerNode($class);
    }
}
