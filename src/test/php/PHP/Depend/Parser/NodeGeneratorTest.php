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

namespace PHP\Depend\Parser;

require_once __DIR__ . '/../AbstractTest.php';

/**
 * Test case for the node generator class.
 *
 * @category  QualityAssurance
 * @author    Manuel Pichler <mapi@pdepend.org>
 * @copyright 2008-2012 Manuel Pichler. All rights reserved.
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version   Release: @package_version@
 * @link      http://pdepend.org/
 *
 * @covers \PHP\Depend\Parser\NodeGenerator
 * @group  pdepend
 * @group  pdepend::parser
 * @group  unittest
 * @group  2.0
 */
class NodeGeneratorTest extends \PHP_Depend_AbstractTest
{
    /**
     * @var \PHP\Depend\Parser\NodeGenerator
     */
    private $generator;

    /**
     * Initializes the node generator test fixture.
     *
     * @return void
     */
    protected function setUp()
    {
        parent::setUp();

        $this->generator = new NodeGenerator();
    }

    /**
     * testTranslatesStaticReference
     *
     * @param \PHPParser_Node $type
     * @param \PHPParser_Node $expr
     * @return void
     * @dataProvider getStaticReferences
     */
    public function testTranslatesStaticReference($type, $expr)
    {
        $this->generator->enterNode($type);
        $this->generator->enterNode($expr);
        $this->generator->leaveNode($expr);

        $result = $this->generator->leaveNode($type);

        $this->assertSame($result->stmts[0], $expr->typeRef->getType());
    }

    /**
     * Returns test class statements with a corresponding static expr nodes.
     *
     * @return array
     */
    public static function getStaticReferences()
    {
        return self::getReferences('static');
    }

    /**
     * testTranslatesSelfReference
     *
     * @param \PHPParser_Node $type
     * @param \PHPParser_Node $expr
     * @return void
     * @dataProvider getSelfReferences
     */
    public function testTranslatesSelfReference($type, $expr)
    {
        $this->generator->enterNode($type);
        $this->generator->enterNode($expr);
        $this->generator->leaveNode($expr);

        $result = $this->generator->leaveNode($type);

        $this->assertSame($result->stmts[0], $expr->typeRef->getType());
    }

    /**
     * Returns test class statements with a corresponding self expr nodes.
     *
     * @return array
     */
    public static function getSelfReferences()
    {
        return self::getReferences('self');
    }

    /**
     * testTranslatesParentReference
     *
     * @param \PHPParser_Node $type
     * @param \PHPParser_Node $parent
     * @param \PHPParser_Node $expr
     * @return void
     * @dataProvider getParentReferences
     */
    public function testTranslatesParentReference($type, $parent, $expr)
    {
        $this->generator->enterNode($parent);
        $result = $this->generator->leaveNode($parent);

        $this->generator->enterNode($type);
        $this->generator->enterNode($expr);
        $this->generator->leaveNode($expr);
        $this->generator->leaveNode($type);

        $this->assertSame($result->stmts[0], $expr->typeRef->getType());
    }

    /**
     * Returns test class statements with a corresponding parent expr nodes.
     *
     * @return array
     */
    public static function getParentReferences()
    {
        $parent = new \PHPParser_Node_Stmt_Class(
            'Owner',
            array('namespacedName' => 'Parent'),
            array('id' => 'Parent#c')
        );

        return array_map(
            function(array $data) use ($parent) {
                $data[0]->extends = $parent->namespacedName;

                return array(
                    $data[0],
                    $parent,
                    $data[1]
                );
            },
            self::getReferences('parent')
        );
    }

    /**
     * Returns test class statements with a corresponding static expr nodes.
     *
     * @param string $specialName
     *
     * @return array
     */
    public static function getReferences($specialName)
    {
        $class = new \PHPParser_Node_Stmt_Class(
            'Owner',
            array('namespacedName' => 'Owner'),
            array('id' => 'Owner#c')
        );

        return array(
            array(
                $class,
                new \PHPParser_Node_Expr_StaticCall(
                    new \PHPParser_Node_Name($specialName), 'foo'
                )
            ),
            array(
                $class,
                new \PHPParser_Node_Expr_StaticPropertyFetch(
                    new \PHPParser_Node_Name($specialName), 'bar'
                )
            ),
            array(
                $class,
                new \PHPParser_Node_Expr_ClassConstFetch(
                    new \PHPParser_Node_Name($specialName), 'BAZ'
                )
            ),
            array(
                $class,
                new \PHPParser_Node_Expr_New(
                    new \PHPParser_Node_Name($specialName)
                )
            ),
            array(
                $class,
                new \PHPParser_Node_Expr_Instanceof(
                    new \PHPParser_Node_Expr_Variable('foo'),
                    new \PHPParser_Node_Name($specialName)
                )
            ),
            array(
                $class,
                new \PHPParser_Node_Param(
                    'bar',
                    null,
                    new \PHPParser_Node_Name($specialName)
                )
            ),
        );
    }
}