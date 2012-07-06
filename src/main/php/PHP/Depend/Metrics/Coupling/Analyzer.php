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
 * @subpackage Metrics
 * @author     Manuel Pichler <mapi@pdepend.org>
 * @copyright  2008-2012 Manuel Pichler. All rights reserved.
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    SVN: $Id$
 * @link       http://pdepend.org/
 */

use \PHP\Depend\AST\ASTClass;
use \PHP\Depend\AST\ASTFunction;
use \PHP\Depend\AST\ASTInterface;
use \PHP\Depend\AST\Property;

/**
 * This analyzer collects coupling values for the hole project. It calculates
 * all function and method <b>calls</b> and the <b>fanout</b>, that means the
 * number of referenced types.
 *
 * The FANOUT calculation is based on the definition used by the apache maven
 * project.
 *
 * <ul>
 *   <li>field declarations (Uses doc comment annotations)</li>
 *   <li>formal parameters and return types (The return type uses doc comment
 *   annotations)</li>
 *   <li>throws declarations (Uses doc comment annotations)</li>
 *   <li>local variables</li>
 * </ul>
 *
 * http://www.jajakarta.org/turbine/en/turbine/maven/reference/metrics.html
 *
 * The implemented algorithm counts each type only once for a method and function.
 * Any type that is either a supertype or a subtype of the class is not counted.
 *
 * @category   QualityAssurance
 * @package    PHP_Depend
 * @subpackage Metrics
 * @author     Manuel Pichler <mapi@pdepend.org>
 * @copyright  2008-2012 Manuel Pichler. All rights reserved.
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    Release: @package_version@
 * @link       http://pdepend.org/
 */
class PHP_Depend_Metrics_Coupling_Analyzer
    extends PHP_Depend_Metrics_AbstractAnalyzer
    implements PHP_Depend_Metrics_NodeAware,
               PHP_Depend_Metrics_ProjectAware
{
    /**
     * Type of this analyzer class.
     */
    const CLAZZ = __CLASS__;

    /**
     * Metrics provided by the analyzer implementation.
     */
    const M_CALLS = 'calls',
        M_FANOUT  = 'fanout',
        M_CA      = 'ca',
        M_CBO     = 'cbo',
        M_CE      = 'ce';

    /**
     * Used subtree serializer.
     *
     * @var PHPParser_PrettyPrinterAbstract
     */
    private $serializer;

    /**
     * Stack of context nodes.
     *
     * @var PHP_Depend_AST_Node[]
     */
    private $nodeStack = array();

    /**
     * Currently active context nodes.
     *
     * @var PHP_Depend_AST_Node
     */
    private $currentNode;

    /**
     * The number of method or function calls.
     *
     * @var integer
     */
    private $calls = 0;

    /**
     * Number of fanouts.
     *
     * @var integer
     */
    private $fanout = 0;

    /**
     * Strings identifying the calls done within a class.
     *
     * @var array
     */
    private $invokes = array();

    /**
     * Temporary map that is used to hold the uuid combinations of dependee and
     * depender.
     *
     * @var array(string=>array)
     * @since 0.10.2
     */
    private $couplingMap = array();

    /**
     * This array holds a mapping between node identifiers and an array with
     * the node's metrics.
     *
     * @var array(string=>array)
     * @since 0.10.2
     */
    private $metrics = array();

    /**
     * Constructs a new serializer instance.
     *
     * @param array $options
     */
    public function __construct(array $options = array())
    {
        parent::__construct($options);

        $this->serializer = new PHPParser_PrettyPrinter_Zend();
    }


    /**
     * Provides the project summary as an <b>array</b>.
     *
     * <code>
     * array(
     *     'calls'   =>  23,
     *     'fanout'  =>  42
     * )
     * </code>
     *
     * @return array(string=>mixed)
     */
    public function getProjectMetrics()
    {
        return array(
            self::M_CALLS   => $this->calls,
            self::M_FANOUT  => $this->fanout
        );
    }

    /**
     * This method will return an <b>array</b> with all generated metric values
     * for the given node or node identifier. If there are no metrics for the
     * requested node, this method will return an empty <b>array</b>.
     *
     * <code>
     * array(
     *     'noc'  =>  23,
     *     'nom'  =>  17,
     *     'nof'  =>  42
     * )
     * </code>
     *
     * @param PHP_Depend_AST_Node|string $node The context node instance.
     *
     * @return array
     */
    public function getNodeMetrics($node)
    {
        $nodeId = (string)is_object($node) ? $node->getId() : $node;

        if (isset($this->couplingMap[$nodeId])) {
            return array(
                self::M_CA  => count($this->couplingMap[$nodeId][self::M_CA]),
                self::M_CBO => count($this->couplingMap[$nodeId][self::M_CE]),
                self::M_CE  => count($this->couplingMap[$nodeId][self::M_CE]),
            );
        }
        return array();
    }

    /**
     * This method takes the temporary coupling map with node UUIDs and calculates
     * the concrete node metrics.
     *
     * @return void
     * @since 0.10.2
     */
    private function postProcessCouplingMap()
    {
        foreach ($this->couplingMap as $uuid => $metrics) {
            $afferentCoupling = count($metrics[self::M_CA]);
            $efferentCoupling = count($metrics[self::M_CE]);

            $this->metrics[$uuid] = array(
                self::M_CA   => $afferentCoupling,
                self::M_CBO  => $efferentCoupling,
                self::M_CE   => $efferentCoupling
            );

            $this->fanout += $efferentCoupling;
        }

        $this->couplingMap = array();
    }

    /**
     * Visits the given compilation unit ast node.
     *
     * @param PHP_Depend_AST_CompilationUnit $unit
     *
     * @return void
     */
    public function visitCompilationUnitBefore(PHP_Depend_AST_CompilationUnit $unit)
    {
        $this->nodeStack[] = $this->currentNode = $unit;
    }

    /**
     * Visits the given compilation unit.
     *
     * @return void
     */
    public function visitCompilationUnitAfter()
    {
        $this->nodeStack   = array();
        $this->currentNode = null;
    }

    /**
     * Visits the given function and calculates it's dependency data.
     *
     * @param \PHP\Depend\AST\ASTFunction $function
     *
     * @return void
     */
    public function visitASTFunctionBefore(ASTFunction $function)
    {
        $this->nodeStack[] = $this->currentNode = $function;

        $this->fireStartFunction($function);

        $this->calculateCoupling($function->getReturnType());

        foreach ($function->thrownExceptions as $type) {
            $this->calculateCoupling($type);
        }
        foreach ($function->params as $param) {
            $this->calculateCoupling($param->typeRef);
        }

        $this->fireEndFunction($function);
    }

    /**
     * Visits the given function ast node.
     *
     * @return void
     */
    public function visitASTFunctionAfter()
    {
        array_pop($this->nodeStack);

        $this->currentNode = end($this->nodeStack);

        $this->calls += count(array_unique($this->invokes));
        $this->invokes = array();
    }

    /**
     * Visits the given class and initializes it's dependencies.
     *
     * @param \PHP\Depend\AST\ASTClass $class
     *
     * @return void
     */
    public function visitASTClassBefore(ASTClass $class)
    {
        $this->nodeStack[] = $this->currentNode = $class;

        $this->initCouplingMap($class);
    }

    /**
     * Visits the given class ast node.
     *
     * @return void
     */
    public function visitASTClassAfter()
    {
        array_pop($this->nodeStack);

        $this->currentNode = end($this->nodeStack);

        $this->calls += count(array_unique($this->invokes));
        $this->invokes = array();
    }

    /**
     * Visits a interface ast node.
     *
     * @param \PHP\Depend\AST\ASTInterface $interface
     *
     * @return mixed
     */
    public function visitASTInterfaceBefore(ASTInterface $interface)
    {
        $this->nodeStack[] = $this->currentNode = $interface;

        $this->initCouplingMap($interface);
    }

    /**
     * Visits a interface ast node.
     *
     * @return void
     */
    public function visitASTInterfaceAfter()
    {
        array_pop($this->nodeStack);

        $this->currentNode = end($this->nodeStack);
    }

    /**
     * Visits the given method and calculates it's dependency data.
     *
     * @param PHP_Depend_AST_Method $method
     *
     * @return void
     */
    public function visitMethodBefore(PHP_Depend_AST_Method $method)
    {
        $this->fireStartMethod($method);

        $this->calculateCoupling($method->getReturnType());

        foreach ($method->thrownExceptions as $type) {
            $this->calculateCoupling($type);
        }
        foreach ($method->params as $param) {
            $this->calculateCoupling($param->typeRef);
        }

        $this->fireEndMethod($method);
    }

    /**
     * Visits a property node.
     *
     * @param \PHP\Depend\AST\Property $property
     *
     * @return void
     */
    public function visitPropertyBefore(Property $property)
    {
        $this->fireStartProperty($property);

        $this->calculateCoupling($property->getType());

        $this->fireEndProperty($property);
    }

    /**
     * Visits a catch statement that will contain a class reference.
     *
     * @param PHPParser_Node_Stmt_Catch $catch
     *
     * @return void
     */
    public function visitStmtCatchBefore(PHPParser_Node_Stmt_Catch $catch)
    {
        $this->calculateCoupling($catch->typeRef);
    }

    /**
     * Visits an instance allocation node.
     *
     * @param PHPParser_Node_Expr_New $new
     *
     * @return void
     */
    public function visitExprNewBefore(PHPParser_Node_Expr_New $new)
    {
        $this->calculateCoupling($new->typeRef);
    }

    /**
     * Visits an instanceof ast node.
     *
     * @param PHPParser_Node_Expr_Instanceof $instanceof
     *
     * @return void
     */
    public function visitExprInstanceofBefore(PHPParser_Node_Expr_Instanceof $instanceof)
    {
        $this->calculateCoupling($instanceof->typeRef);
    }

    /**
     * Visits a static method call node.
     *
     * @param PHPParser_Node_Expr_StaticCall $call
     *
     * @return void
     */
    public function visitExprStaticCallBefore(PHPParser_Node_Expr_StaticCall $call)
    {
        $this->calculateCoupling($call->typeRef);

        $this->updateInvokes($call);
    }

    /**
     * Visits the given class property fetch ast node.
     *
     * @param PHPParser_Node_Expr_StaticPropertyFetch $fetch
     *
     * @return void
     */
    public function visitExprStaticPropertyFetchBefore(PHPParser_Node_Expr_StaticPropertyFetch $fetch)
    {
        $this->calculateCoupling($fetch->typeRef);
    }

    /**
     * Visits the given class constant fetch ast node.
     *
     * @param PHPParser_Node_Expr_ClassConstFetch $fetch
     *
     * @return void
     */
    public function visitExprClassConstFetchBefore(PHPParser_Node_Expr_ClassConstFetch $fetch)
    {
        $this->calculateCoupling($fetch->typeRef);
    }

    /**
     * Visits a function call ast node.
     *
     * @param PHPParser_Node_Expr_FuncCall $call
     *
     * @return void
     */
    public function visitExprFuncCallBefore(PHPParser_Node_Expr_FuncCall $call)
    {
        $this->updateInvokes($call);
    }

    /**
     * Visits an object method call.
     *
     * @param PHPParser_Node_Expr_MethodCall $call
     *
     * @return void
     */
    public function visitExprMethodCallBefore(PHPParser_Node_Expr_MethodCall $call)
    {
        $this->updateInvokes($call);
    }

    /**
     * Updates the number of invokes.
     *
     * @param PHPParser_Node_Expr $expr
     *
     * @return void
     */
    private function updateInvokes(PHPParser_Node_Expr $expr)
    {
        $clone       = clone $expr;
        $clone->args = array();

        $this->invokes[] = $this->serializer->prettyPrintExpr($clone);
    }

    /**
     * Calculates the coupling between the given types.
     *
     * @param PHP_Depend_AST_Type $afferentType
     *
     * @return void
     * @since 0.10.2
     */
    private function calculateCoupling(PHP_Depend_AST_Type $afferentType = null)
    {
        if (null === $afferentType) {
            return;
        }

        if ($this->currentNode instanceof PHP_Depend_AST_Type && (
            $afferentType->isSubtypeOf($this->currentNode) ||
                $this->currentNode->isSubtypeOf($afferentType))
        ) {
            return;
        }

        $afferentId = $afferentType->getId();
        $efferentId = $this->currentNode->getId();

        $this->initCouplingMap($afferentType);
        if (!isset($this->couplingMap[$afferentId][self::M_CA][$efferentId])) {
            $this->couplingMap[$afferentId][self::M_CA][$efferentId] = true;
            ++$this->fanout;
        }

        if (!($this->currentNode instanceof PHP_Depend_AST_Type)) {
            return;
        }

        $this->couplingMap[$efferentId][self::M_CE][$afferentId] = true;
    }

    /**
     * This method will initialize a temporary coupling container for the given
     * given class or interface instance.
     *
     * @param PHP_Depend_AST_Type $type
     *
     * @return void
     * @since 0.10.2
     */
    private function initCouplingMap(PHP_Depend_AST_Type $type)
    {
        if (isset($this->couplingMap[$type->getId()])) {
            return;
        }

        $this->couplingMap[$type->getId()] = array(
            self::M_CE => array(),
            self::M_CA => array()
        );
    }
}
