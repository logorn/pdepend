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

namespace PHP\Depend\Input;

use \PHP\Depend\AbstractTest;

/**
 * Test case for the composite filter.
 *
 * @category  QualityAssurance
 * @author    Manuel Pichler <mapi@pdepend.org>
 * @copyright 2008-2012 Manuel Pichler. All rights reserved.
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version   Release: @package_version@
 * @link      http://pdepend.org/
 *
 * @covers \PHP\Depend\Input\CompositeFilter
 * @group  pdepend
 * @group  pdepend::input
 * @group  unittest
 */
class CompositeFilterTest extends AbstractTest
{
    /**
     * testCompositeInvokesFirstAcceptInFilterChain
     *
     * @return void
     */
    public function testCompositeInvokesFirstAcceptInFilterChain()
    {
        $filter0 = new DummyFilter(true);

        $composite = new CompositeFilter();
        $composite->append($filter0);

        $composite->accept(__DIR__, __DIR__);

        $this->assertTrue($filter0->invoked);
    }

    /**
     * testCompositeInvokesNextAcceptIfPreviousAcceptReturnsTrue
     *
     * @return void
     */
    public function testCompositeInvokesNextAcceptIfPreviousAcceptReturnsTrue()
    {
        $filter0 = new DummyFilter(true);
        $filter1 = new DummyFilter(true);

        $composite = new CompositeFilter();
        $composite->append($filter0);
        $composite->append($filter1);

        $composite->accept(__DIR__, __DIR__);

        $this->assertTrue($filter1->invoked);
    }

    /**
     * testCompositeNotInvokesNextAcceptIfPreviousAcceptReturnsTrue
     *
     * @return void
     */
    public function testCompositeNotInvokesNextAcceptIfPreviousAcceptReturnsTrue()
    {
        $filter0 = new DummyFilter(false);
        $filter1 = new DummyFilter(true);

        $composite = new CompositeFilter();
        $composite->append($filter0);
        $composite->append($filter1);

        $composite->accept(__DIR__, __DIR__);

        $this->assertFalse($filter1->invoked);
    }
}
