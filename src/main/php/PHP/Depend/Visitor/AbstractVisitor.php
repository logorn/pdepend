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
 * This abstract visitor implementation provides a default traversal algorithm
 * that can be used for custom visitors.
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
abstract class PHP_Depend_Visitor_AbstractVisitor
{
    /**
     * List of all registered listeners.
     *
     * @var array(PHP_Depend_Visitor_ListenerI) $_listeners
     */
    private $_listeners = array();

    /**
     * Returns an iterator with all registered visit listeners.
     *
     * @return Iterator
     */
    public function getVisitListeners()
    {
        return new ArrayIterator($this->_listeners);
    }

    /**
     * Adds a new listener to this node visitor.
     *
     * @param PHP_Depend_Visitor_ListenerI $listener The new visit listener.
     * @return void
     */
    public function addVisitListener(PHP_Depend_Visitor_ListenerI $listener)
    {
        if (in_array($listener, $this->_listeners, true) === false) {
            $this->_listeners[] = $listener;
        }
    }

    /**
     * Visits a class node.
     *
     * @param \PHP\Depend\AST\ASTClass $class
     *
     * @return void
     */
    public function visitClass(ASTClass $class)
    {
        $this->fireStartClass($class);

        $class->getSourceFile()->accept($this);

        foreach ($class->getProperties() as $property) {
            $property->accept($this);
        }
        foreach ($class->getMethods() as $method) {
            $method->accept($this);
        }

        $this->fireEndClass($class);
    }

    /**
     * Visits a trait node.
     *
     * @param PHP_Depend_AST_Trait $trait The current trait node.
     * @return void
     * @since 1.0.0
     */
    public function visitTrait(PHP_Depend_AST_Trait $trait)
    {
        $this->fireStartTrait($trait);

        $trait->getSourceFile()->accept($this);

        foreach ($trait->getMethods() as $method) {
            $method->accept($this);
        }

        $this->fireEndTrait($trait);
    }

    /**
     * Visits a file node.
     *
     * @param PHP_Depend_AST_File $file The current file node.
     * @return void
     */
    public function visitFile(PHP_Depend_AST_File $file)
    {
        $this->fireStartFile($file);
        $this->fireEndFile($file);
    }

    /**
     * Visits a function node.
     *
     * @param \PHP\Depend\AST\ASTFunction $function
     *
     * @return void
     */
    public function visitFunction(ASTFunction $function)
    {
        $this->fireStartFunction($function);

        $function->getSourceFile()->accept($this);

        foreach ($function->getParameters() as $parameter) {
            $parameter->accept($this);
        }

        $this->fireEndFunction($function);
    }

    /**
     * Visits a code interface object.
     *
     * @param \PHP\Depend\AST\ASTInterface $interface
     * @return void
     */
    public function visitInterface(ASTInterface $interface)
    {
        $this->fireStartInterface($interface);

        $interface->getSourceFile()->accept($this);

        foreach ($interface->getMethods() as $method) {
            $method->accept($this);
        }

        $this->fireEndInterface($interface);
    }

    /**
     * Visits a method node.
     *
     * @param \PHP\Depend\AST\ASTMethod $method
     *
     * @return void
     */
    public function visitMethod(ASTMethod $method)
    {
        $this->fireStartMethod($method);

        foreach ($method->getParameters() as $parameter) {
            $parameter->accept($this);
        }

        $this->fireEndMethod($method);
    }

    /**
     * Visits a package node.
     *
     * @param PHP_Depend_AST_Package $package
     * @return void
     */
    public function visitPackage(PHP_Depend_AST_Package $package)
    {
        $this->fireStartPackage($package);

        foreach ($package->getClasses() as $class) {
            $class->accept($this);
        }
        foreach ($package->getInterfaces() as $interface) {
            $interface->accept($this);
        }
        foreach ($package->getTraits() as $trait) {
            $trait->accept($this);
        }
        foreach ($package->getFunctions() as $function) {
            $function->accept($this);
        }

        $this->fireEndPackage($package);
    }

    /**
     * Visits a parameter node.
     *
     * @param PHP_Depend_AST_Parameter $parameter The parameter node.
     * @return void
     */
    public function visitParameter(PHP_Depend_AST_Parameter $parameter)
    {
        $this->fireStartParameter($parameter);
        $this->fireEndParameter($parameter);
    }

    /**
     * Visits a property node.
     *
     * @param \PHP\Depend\AST\Property $property The property class node.
     * @return void
     */
    public function visitProperty(Property $property)
    {
        $this->fireStartProperty($property);
        $this->fireEndProperty($property);
    }

    /**
     * Sends a start class event.
     *
     * @param \PHP\Depend\AST\ASTClass $class
     *
     * @return void
     */
    protected function fireStartClass(ASTClass $class)
    {
        foreach ($this->_listeners as $listener) {
            $listener->startVisitClass($class);
        }
    }

    /**
     * Sends an end class event.
     *
     * @param \PHP\Depend\AST\ASTClass $class
     *
     * @return void
     */
    protected function fireEndClass(ASTClass $class)
    {
        foreach ($this->_listeners as $listener) {
            $listener->endVisitClass($class);
        }
    }

    /**
     * Sends a start trait event.
     *
     * @param PHP_Depend_AST_Trait $trait The context trait instance.
     * @return void
     */
    protected function fireStartTrait(PHP_Depend_AST_Trait $trait)
    {
        foreach ($this->_listeners as $listener) {
            $listener->startVisitTrait($trait);
        }
    }

    /**
     * Sends an end trait event.
     *
     * @param PHP_Depend_AST_Trait $trait The context trait instance.
     * @return void
     */
    protected function fireEndTrait(PHP_Depend_AST_Trait $trait)
    {
        foreach ($this->_listeners as $listener) {
            $listener->endVisitTrait($trait);
        }
    }

    /**
     * Sends a start file event.
     *
     * @param PHP_Depend_AST_File $file The context file.
     *
     * @return void
     */
    protected function fireStartFile(PHP_Depend_AST_File $file)
    {
        foreach ($this->_listeners as $listener) {
            $listener->startVisitFile($file);
        }
    }

    /**
     * Sends an end file event.
     *
     * @param PHP_Depend_AST_File $file The context file instance.
     *
     * @return void
     */
    protected function fireEndFile(PHP_Depend_AST_File $file)
    {
        foreach ($this->_listeners as $listener) {
            $listener->endVisitFile($file);
        }
    }

    /**
     * Sends a start function event.
     *
     * @param \PHP\Depend\AST\ASTFunction $function
     *
     * @return void
     */
    protected function fireStartFunction(ASTFunction $function)
    {
        foreach ($this->_listeners as $listener) {
            $listener->startVisitFunction($function);
        }
    }

    /**
     * Sends an end function event.
     *
     * @param \PHP\Depend\AST\ASTFunction $function
     * @return void
     */
    protected function fireEndFunction(ASTFunction $function)
    {
        foreach ($this->_listeners as $listener) {
            $listener->endVisitFunction($function);
        }
    }

    /**
     * Sends a start interface event.
     *
     * @param \PHP\Depend\AST\ASTInterface $interface
     *
     * @return void
     */
    protected function fireStartInterface(ASTInterface $interface)
    {
        foreach ($this->_listeners as $listener) {
            $listener->startVisitInterface($interface);
        }
    }

    /**
     * Sends an end interface event.
     *
     * @param \PHP\Depend\AST\ASTInterface $interface
     * @return void
     */
    protected function fireEndInterface(ASTInterface $interface)
    {
        foreach ($this->_listeners as $listener) {
            $listener->endVisitInterface($interface);
        }
    }

    /**
     * Sends a start method event.
     *
     * @param \PHP\Depend\AST\ASTMethod $method
     * @return void
     */
    protected function fireStartMethod(ASTMethod $method)
    {
        foreach ($this->_listeners as $listener) {
            $listener->startVisitMethod($method);
        }
    }

    /**
     * Sends an end method event.
     *
     * @param \PHP\Depend\AST\ASTMethod $method
     *
     * @return void
     */
    protected function fireEndMethod(ASTMethod $method)
    {
        foreach ($this->_listeners as $listener) {
            $listener->endVisitMethod($method);
        }
    }

    /**
     * Sends a start package event.
     *
     * @param PHP_Depend_AST_Package $package The context package instance.
     * @return void
     */
    protected function fireStartPackage(PHP_Depend_AST_Package $package)
    {
        foreach ($this->_listeners as $listener) {
            $listener->startVisitPackage($package);
        }
    }

    /**
     * Sends an end package event.
     *
     * @param PHP_Depend_AST_Package $package The context package instance.
     * @return void
     */
    protected function fireEndPackage(PHP_Depend_AST_Package $package)
    {
        foreach ($this->_listeners as $listener) {
            $listener->endVisitPackage($package);
        }
    }

    /**
     * Sends a start parameter event.
     *
     * @param PHP_Depend_AST_Parameter $parameter The context parameter instance.
     *
     * @return void
     */
    protected function fireStartParameter(PHP_Depend_AST_Parameter $parameter)
    {
        foreach ($this->_listeners as $listener) {
            $listener->startVisitParameter($parameter);
        }
    }

    /**
     * Sends a end parameter event.
     *
     * @param PHP_Depend_AST_Parameter $parameter The context parameter instance.
     * @return void
     */
    protected function fireEndParameter(PHP_Depend_AST_Parameter $parameter)
    {
        foreach ($this->_listeners as $listener) {
            $listener->endVisitParameter($parameter);
        }
    }

    /**
     * Sends a start property event.
     *
     * @param \PHP\Depend\AST\Property $property The context property instance.
     * @return void
     */
    protected function fireStartProperty(Property $property)
    {
        foreach ($this->_listeners as $listener) {
            $listener->startVisitProperty($property);
        }
    }

    /**
     * Sends an end property event.
     *
     * @param \PHP\Depend\AST\Property $property The context property instance.
     * @return void
     */
    protected function fireEndProperty(Property $property)
    {
        foreach ($this->_listeners as $listener) {
            $listener->endVisitProperty($property);
        }
    }
}
