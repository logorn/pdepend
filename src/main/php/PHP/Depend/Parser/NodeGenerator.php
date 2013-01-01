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

namespace PHP\Depend\Parser;

use \PHP\Depend\AST\ASTNode;
use \PHP\Depend\AST\ASTType;
use \PHP\Depend\AST\ASTTypeRef;
use \PHP\Depend\AST\ASTClass;
use \PHP\Depend\AST\ASTClassRefs;
use \PHP\Depend\AST\ASTFunction;
use \PHP\Depend\AST\ASTFunctionRefs;
use \PHP\Depend\AST\ASTInterface;
use \PHP\Depend\AST\ASTInterfaceRefs;
use \PHP\Depend\AST\ASTMethod;
use \PHP\Depend\AST\ASTMethodRefs;
use \PHP\Depend\AST\ASTNamespace;
use \PHP\Depend\AST\ASTNamespaceRefs;
use \PHP\Depend\AST\ASTProperties;
use \PHP\Depend\AST\ASTProperty;
use \PHP\Depend\AST\ASTPropertyRefs;
use PHP\Depend\AST\ASTConditionalExpr;
use PHP\Depend\AST\ASTWhileStatement;
use PHP\Depend\AST\ASTBooleanOrExpr;
use PHP\Depend\AST\ASTBooleanAndExpr;
use PHP\Depend\AST\ASTIfStatement;
use PHP\Depend\AST\ASTLogicalAndExpr;
use PHP\Depend\AST\ASTLogicalXorExpr;
use PHP\Depend\AST\ASTLogicalOrExpr;

/**
 * Visitor class that generates custom nodes used by PDepend.
 *
 * @category  QualityAssurance
 * @author    Manuel Pichler <mapi@pdepend.org>
 * @copyright 2008-2012 Manuel Pichler. All rights reserved.
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version   Release: @package_version@
 * @link      http://pdepend.org/
 * @since     2.0.0
 */
class NodeGenerator extends \PHPParser_NodeVisitorAbstract
{
    /**
     * The current source file.
     *
     * @var string
     */
    private $file;

    /**
     * @var string
     */
    private $declaringType;

    /**
     * Qualified name of the parent of the current class.
     *
     * @var string
     */
    private $parentClass;

    /**
     * @var string
     */
    private $declaringNamespace;

    /**
     * @var string
     */
    private $declaringPackage;

    /**
     * Modifiers of a property.
     *
     * @var integer
     */
    private $modifier;

    /**
     * @var \PHP\Depend\Context
     */
    private $context;

    /**
     * Initializes the node context.
     */
    public function __construct()
    {
        $this->context = new \PHP\Depend\Context();
    }

    /**
     * Called when entering a node.
     *
     * Return value semantics:
     *  * null:      $node stays as-is
     *  * otherwise: $node is set to the return value
     *
     * @param \PHPParser_Node $node
     * @return \PHPParser_Node|null
     */
    public function enterNode(\PHPParser_Node $node)
    {
        if ($node instanceof \PHPParser_Node_Stmt_Class ||
            $node instanceof \PHPParser_Node_Stmt_Interface) {

            $this->declaringType    = (string) $node->namespacedName;
            $this->declaringPackage = $this->extractNamespaceName($node);

            $this->parentClass = (string) (is_array($node->extends) ? null :$node->extends);
        } elseif ($node instanceof \PHPParser_Node_Stmt_Namespace) {

            $this->declaringNamespace = (string) $node->name;
        } elseif ($node instanceof \PHPParser_Node_Stmt_Property) {

            $this->modifier = $node->type;
        } elseif ($node instanceof \PHP\Depend\AST\ASTCompilationUnit) {

            $this->file = $node->getName();
        }
    }

    /**
     * Sets a unique identifier on the given node. The ID will be stored in a
     * node attribute named <b>"id"</b>.
     *
     * @param \PHPParser_Node $node Node
     * @return null|\PHPParser_Node
     */
    public function leaveNode(\PHPParser_Node $node)
    {
        $newNode = null;
        if ($node instanceof \PHPParser_Node_Stmt_Namespace) {

            $newNode = new ASTNamespace(
                $node,
                new ASTNamespaceRefs($this->context)
            );

            $this->declaringNamespace = null;
        } elseif ($node instanceof \PHPParser_Node_Stmt_Class) {

            $implemented = array();
            foreach ($node->implements as $implements) {

                $implemented[] = (string) $implements;
            }

            $newNode = $this->wrapOptionalNamespace(
                new ASTClass(
                    $node,
                    new ASTClassRefs(
                        $this->context,
                        $this->extractNamespaceName($node),
                        $this->parentClass,
                        $implemented
                    )
                )
            );

            $this->declaringType    = null;
            $this->declaringPackage = null;
        } elseif ($node instanceof \PHPParser_Node_Stmt_Interface) {

            $extends = array();
            foreach ($node->extends as $extend) {

                $extends[] = (string) $extend;
            }

            $newNode = $this->wrapOptionalNamespace(
                new ASTInterface(
                    $node,
                    new ASTInterfaceRefs(
                        $this->context,
                        $this->extractNamespaceName($node),
                        $extends
                    )
                )
            );

            $this->declaringType    = null;
            $this->declaringPackage = null;
        } elseif ($node instanceof \PHPParser_Node_Stmt_Property) {

            $this->modifier = 0;

            $newNode = new ASTProperties($node);
        } elseif ($node instanceof \PHPParser_Node_Stmt_PropertyProperty) {

            $newNode = new ASTProperty(
                $node,
                new ASTPropertyRefs(
                    $this->context,
                    $this->extractNamespaceName($node),
                    $this->declaringType,
                    (string) $node->typeName
                ),
                $this->modifier
            );
        } elseif ($node instanceof \PHPParser_Node_Stmt_ClassMethod) {

            $thrownExceptions = array();
            foreach ($node->exceptions as $exception) {

                $thrownExceptions[] = new ASTTypeRef(
                    $this->context,
                    (string) $exception
                );
            }

            $newNode = new ASTMethod(
                $node,
                array(
                    'thrownExceptions'  => $thrownExceptions
                ),
                new ASTMethodRefs(
                    $this->context,
                    $this->extractNamespaceName($node),
                    $this->declaringType,
                    (string) $node->returnType
                )
            );

        } elseif ($node instanceof \PHPParser_Node_Stmt_Function) {

            $thrownExceptions = array();
            foreach ($node->exceptions as $exception) {

                $thrownExceptions[] = new ASTTypeRef(
                    $this->context,
                    (string) $exception
                );
            }

            $newNode = $this->wrapOptionalNamespace(
                new ASTFunction(
                    $node,
                    array(
                        'thrownExceptions' => $thrownExceptions
                    ),
                    new ASTFunctionRefs(
                        $this->context,
                        $this->extractNamespaceName($node),
                        (string) $node->returnType
                    )
                )
            );

            $this->declaringPackage = null;
        } elseif ($node instanceof \PHPParser_Node_Stmt_Catch) {

            $node->typeRef = new ASTTypeRef(
                $this->context,
                (string) $node->type
            );
        } elseif ($node instanceof \PHPParser_Node_Expr_StaticCall
            || $node instanceof \PHPParser_Node_Expr_StaticPropertyFetch
            || $node instanceof \PHPParser_Node_Expr_ClassConstFetch
            || $node instanceof \PHPParser_Node_Expr_New
            || $node instanceof \PHPParser_Node_Expr_Instanceof) {

            if ($node->class instanceof \PHPParser_Node_Name) {

                $node->typeRef = new ASTTypeRef(
                    $this->context,
                    $this->resolveSpecialName($node->class)
                );
            } else {

                $node->typeRef = null;
            }
        } elseif ($node instanceof \PHPParser_Node_Param) {

            if ($node->type instanceof \PHPParser_Node_Name) {

                $node->typeRef = new ASTTypeRef(
                    $this->context,
                    $this->resolveSpecialName($node->type)
                );
            } else {

                $node->typeRef = null;
            }
        } elseif ($node instanceof \PHPParser_Node_Expr_Ternary) {
            $newNode = new ASTConditionalExpr($node);
        } elseif ($node instanceof \PHPParser_Node_Expr_BooleanAnd) {
            $newNode = new ASTBooleanAndExpr($node);
        } elseif ($node instanceof \PHPParser_Node_Expr_BooleanOr) {
            $newNode = new ASTBooleanOrExpr($node);
        } elseif ($node instanceof \PHPParser_Node_Expr_LogicalAnd) {
            $newNode = new ASTLogicalAndExpr($node);
        } elseif ($node instanceof \PHPParser_Node_Expr_LogicalOr) {
            $newNode = new ASTLogicalOrExpr($node);
        } elseif ($node instanceof \PHPParser_Node_Expr_LogicalXor) {
            $newNode = new ASTLogicalXorExpr($node);
        } elseif ($node instanceof \PHPParser_Node_Stmt_If) {
            $newNode = new ASTIfStatement($node);
        } elseif ($node instanceof \PHPParser_Node_Stmt_While) {
            $newNode = new ASTWhileStatement($node);
        }

        if ($newNode instanceof \PHP\Depend\AST\ASTFragment) {

            $newNode->setAttribute('file', $this->file);
        }

        return $newNode;
    }

    /**
     * Translates PHP's special class names like 'static', 'self' or 'parent'
     * into the real class names.
     *
     * @param string $name
     * @return string
     */
    private function resolveSpecialName($name)
    {
        if (0 == strcasecmp($name, 'self') ||
            0 == strcasecmp($name, 'static')) {

            return (string) $this->declaringType ?: $name;
        } else if (0 === strcasecmp($name, 'parent')) {

            return (string) $this->parentClass ?: $name;
        }

        return (string) $name;
    }

    /**
     * Extracts the best matching namespace for the given node.
     *
     * This method first looks for a currently active namespace. If this exists
     * it will return the namespace name. Then this method tries to extract a
     * package tag from the node's doc comment. If it exists this tag is used.
     * Then it tries to reuse a previously extracted package tag. And finally
     * this method returns the pseudo global namespace.
     *
     * @param \PHPParser_Node $node
     * @return string
     */
    private function extractNamespaceName(\PHPParser_Node $node)
    {
        if (is_string($this->declaringNamespace)) {

            return $this->declaringNamespace;
        } else if (preg_match('(\*\s*@package\s+([^\s\*]+))', $node->getDocComment(), $match)) {

            return ($this->declaringPackage = $match[1]);
        } else if (is_string($this->declaringPackage)) {

            return $this->declaringPackage;
        }

        return ($this->declaringPackage = "+global");
    }

    /**
     * This method will wrap the given node with a pseudo namespace object,
     * when the node itself is not within a valid php namespace.
     *
     * @param \PHP\Depend\AST\ASTNode $node
     * @return \PHP\Depend\AST\ASTNamespace|\PHP\Depend\AST\ASTNode
     */
    private function wrapOptionalNamespace(ASTNode $node)
    {
        if (is_string($this->declaringNamespace)) {

            return $node;
        }

        return new ASTNamespace(
            new \PHPParser_Node_Stmt_Namespace(
                new \PHPParser_Node_Name($this->extractNamespaceName($node)),
                array($node),
                array('id'  => $this->extractNamespaceName($node) . '#n')
            ),
            new ASTNamespaceRefs($this->context)
        );
    }
}
