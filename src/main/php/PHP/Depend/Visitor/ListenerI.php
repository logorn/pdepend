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
 * @subpackage Visitor
 * @author     Manuel Pichler <mapi@pdepend.org>
 * @copyright  2008-2012 Manuel Pichler. All rights reserved.
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    SVN: $Id$
 * @link       http://pdepend.org/
 */

use \PHP\Depend\AST\ASTClass;
use \PHP\Depend\AST\ASTFunction;
use \PHP\Depend\AST\ASTInterface;
use \PHP\Depend\AST\ASTMethod;
use \PHP\Depend\AST\Property;

/**
 * Base interface for a visitor listener.
 *
 * @category   QualityAssurance
 * @package    PHP_Depend
 * @subpackage Visitor
 * @author     Manuel Pichler <mapi@pdepend.org>
 * @copyright  2008-2012 Manuel Pichler. All rights reserved.
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    Release: @package_version@
 * @link       http://pdepend.org/
 */
interface PHP_Depend_Visitor_ListenerI
{
    /**
     * Is called when the visitor starts a new class instance.
     *
     * @param \PHP\Depend\AST\ASTClass $class
     *
     * @return void
     */
    public function startVisitClass(ASTClass $class);

    /**
     * Is called when the visitor ends with a class instance.
     *
     * @param \PHP\Depend\AST\ASTClass $class
     *
     * @return void
     */
    public function endVisitClass(ASTClass $class);

    /**
     * Is called when the visitor starts a new trait instance.
     *
     * @param PHP_Depend_AST_Trait $trait The context trait instance.
     *
     * @return void
     * @since 1.0.0
     */
    function startVisitTrait(PHP_Depend_AST_Trait $trait);

    /**
     * Is called when the visitor ends with a trait instance.
     *
     * @param PHP_Depend_AST_Trait $trait The context trait instance.
     *
     * @return void
     * @since 1.0.0
     */
    function endVisitTrait(PHP_Depend_AST_Trait $trait);

    /**
     * Is called when the visitor starts a new file instance.
     *
     * @param PHP_Depend_AST_File $file The context file instance.
     *
     * @return void
     */
    function startVisitFile(PHP_Depend_AST_File $file);

    /**
     * Is called when the visitor ends with a file instance.
     *
     * @param PHP_Depend_AST_File $file The context file instance.
     *
     * @return void
     */
    function endVisitFile(PHP_Depend_AST_File $file);

    /**
     * Is called when the visitor starts a new function instance.
     *
     * @param \PHP\Depend\AST\ASTFunction $function
     *
     * @return void
     */
    public function startVisitFunction(ASTFunction $function);

    /**
     * Is called when the visitor ends with a function instance.
     *
     * @param \PHP\Depend\AST\ASTFunction $function
     *
     * @return void
     */
    public function endVisitFunction(ASTFunction $function);

    /**
     * Is called when the visitor starts a new interface instance.
     *
     * @param \PHP\Depend\AST\ASTInterface $interface
     *
     * @return void
     */
    public function startVisitInterface(ASTInterface $interface);

    /**
     * Is called when the visitor ends with an interface instance.
     *
     * @param \PHP\Depend\AST\ASTInterface $interface
     *
     * @return void
     */
    public function endVisitInterface(ASTInterface $interface);

    /**
     * Is called when the visitor starts a new method instance.
     *
     * @param \PHP\Depend\AST\ASTMethod $method
     *
     * @return void
     */
    public function startVisitMethod(ASTMethod $method);

    /**
     * Is called when the visitor ends with a method instance.
     *
     * @param \PHP\Depend\AST\ASTMethod $method
     *
     * @return void
     */
    public function endVisitMethod(ASTMethod $method);

    /**
     * Is called when the visitor starts a new package instance.
     *
     * @param PHP_Depend_AST_Package $package The context package instance.
     *
     * @return void
     */
    function startVisitPackage(PHP_Depend_AST_Package $package);

    /**
     * Is called when the visitor ends with a package instance.
     *
     * @param PHP_Depend_AST_Package $package The context package instance.
     *
     * @return void
     */
    function endVisitPackage(PHP_Depend_AST_Package $package);

    /**
     * Is called when the visitor starts a new parameter instance.
     *
     * @param PHP_Depend_AST_Parameter $parameter The context parameter instance.
     *
     * @return void
     */
    function startVisitParameter(PHP_Depend_AST_Parameter $parameter);

    /**
     * Is called when the visitor ends with a parameter instance.
     *
     * @param PHP_Depend_AST_Package $parameter The context parameter instance.
     *
     * @return void
     */
    function endVisitParameter(PHP_Depend_AST_Parameter $parameter);

    /**
     * Is called when the visitor starts a new property instance.
     *
     * @param \PHP\Depend\AST\Property $property The context property instance.
     *
     * @return void
     */
    function startVisitProperty(Property $property);

    /**
     * Is called when the visitor ends with a property instance.
     *
     * @param \PHP\Depend\AST\Property $property The context property instance.
     *
     * @return void
     */
    function endVisitProperty(Property $property);
}
