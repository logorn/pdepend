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
 * Visitor class that extracts doc comment annotations.
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
class PHP_Depend_Parser_AnnotationExtractor extends PHPParser_NodeVisitor_NameResolver
{
    /**
     * @var null|PHPParser_Node_Name Current namespace
     */
    protected $namespace;

    /**
     * @var array Currently defined namespace and class aliases
     */
    protected $aliases;

    /**
     * @var PHPParser_Node_Name|PHPParser_Node_Name_FullyQualified
     */
    protected $typeName;

    /**
     * Resets some internal state properties.
     *
     * @param PHPParser_Node[] $nodes
     *
     * @return void
     */
    public function beforeTraverse(array $nodes)
    {
        $this->namespace = null;
        $this->aliases   = array();
        $this->typeName      = null;
    }

    /**
     * Sets some additional information about dependent types onto class, method,
     * function etc. nodes.
     *
     * @param PHPParser_Node $node
     *
     * @return void
     * @throws PHPParser_Error
     */
    public function enterNode(PHPParser_Node $node)
    {
        if ($node instanceof PHPParser_Node_Stmt_Namespace) {
            $this->namespace = $node->name;
            $this->aliases   = array();
        } else if ($node instanceof PHPParser_Node_Stmt_UseUse) {
            if (isset($this->aliases[$node->alias])) {
                throw new PHPParser_Error(
                    sprintf(
                        'Cannot use "%s" as "%s" because the name is already in use',
                        $node->name, $node->alias
                    ),
                    $node->getLine()
                );
            }

            $this->aliases[$node->alias] = $node->name;
        } else if ($node instanceof PHPParser_Node_Stmt_Property) {
            $this->typeName = $this->extractType($node, 'var');
            $node->typeName = $this->typeName;

        } else if ($node instanceof PHPParser_Node_Stmt_PropertyProperty) {
            if (null === ($type = $this->extractType($node, 'var'))) {
                $type = $this->typeName;
            }
            $node->typeName = $type;
        } else if ($node instanceof PHPParser_Node_Stmt_Function
            || $node instanceof PHPParser_Node_Stmt_ClassMethod
        ) {
            $node->returnType = $this->extractType($node, 'return');
            $node->exceptions = $this->extractTypes($node, 'throws');
        }
    }

    /**
     * This post visit method resets some internal state properties.
     *
     * @param PHPParser_Node $node
     *
     * @return void
     */
    public function leaveNode(PHPParser_Node $node)
    {
        if ($node instanceof PHPParser_Node_Stmt_Property
            || $node instanceof PHPParser_Node_Stmt_PropertyProperty
        ) {
            $this->typeName = null;
        }
    }

    /**
     * Extracts additional types from the doc comment block of the given
     * <b>$node</b>.
     *
     * @param PHPParser_Node $node
     * @param string         $tag
     *
     * @return null|PHPParser_Node_Name|PHPParser_Node_Name_FullyQualified
     */
    private function extractType(PHPParser_Node $node, $tag)
    {
        if (count($names = $this->extractTypes($node, $tag)) > 0) {
            return reset($names);
        }
        return null;
    }

    /**
     * Extracts additional types from the doc comment block of the given
     * <b>$node</b>.
     *
     * @param PHPParser_Node $node
     * @param string         $tag
     *
     * @return PHPParser_Node_Name[]
     * @todo 2.0 Handle special doc comments like TypeA|TypeB|..., Type[], array(Type)
     */
    private function extractTypes(PHPParser_Node $node, $tag)
    {
        if (!$node->getDocComment()) {
            return array();
        }
        $regexp = sprintf('(\*\s*@%s\s+([^\s]+))', $tag);
        if (0 === preg_match_all($regexp, $node->getDocComment(), $matches)) {
            return array();
        }

        $names = array();
        foreach ($matches[1] as $match) {
            $match = rtrim($match, '[]');

            if (false === PHP_Depend_Util_Type::isScalarType($match)) {
                $names[] = $this->resolveClassName(new PHPParser_Node_Name($match));
            }
        }

        return array_filter($names);
    }

    /**
     * Resolves the full qualified name for the given name.
     *
     * The code of this method was taken from the NameResolver visitor shipped
     * with the PHPParser project.
     *
     * @param PHPParser_Node_Name $name
     *
     * @return PHPParser_Node_Name|PHPParser_Node_Name_FullyQualified
     */
    protected function resolveClassName(PHPParser_Node_Name $name)
    {
        // don't resolve special class names
        if (in_array((string)$name, array('self', 'parent', 'static'))) {
            return $name;
        }

        // fully qualified names are already resolved
        if (0 === strpos($name, '\\') || $name->isFullyQualified()) {
            $name->set(ltrim($name, '\\'));
            return $name;
        }

        // resolve aliases (for non-relative names)
        if (!$name->isRelative() && isset($this->aliases[$name->getFirst()])) {
            $name->setFirst($this->aliases[$name->getFirst()]);
            // if no alias exists prepend current namespace
        } elseif (null !== $this->namespace) {
            $name->prepend($this->namespace);
        }

        return new PHPParser_Node_Name_FullyQualified($name->parts, $name->getAttributes());
    }
}