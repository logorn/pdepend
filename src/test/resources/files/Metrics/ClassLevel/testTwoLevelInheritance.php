<?php
namespace {

    class ClassLevelTwoLevelInherit
        extends \ClassLevelNsOne\InheritClassOne
        implements \ClassLevelNsOne\DirectImplementedInterface
    {
        public $foo, $bar;

        private $baz;

        public function foo($p0)
        {
            if ($p0) {
                return 42;
            } else if (0 === $p0) {
                return 23;
            }
            return 13;
        }

        protected function baz()
        {
            $this->baz = $this->baz ? 0 : $this->inc($this->baz);
        }

        private function inc($value)
        {
            return ++$value;
        }
    }
}

namespace ClassLevelNsOne {

    class InheritClassOne extends InheritClassTwo
    {
        public $foobar, $barfoo;

        protected $baz;

        public function bar()
        {
            if (42 === time() % 0) {

                $this->foobar = 42;
            }
        }

        private function foo()
        {
            $this->baz = 23;
        }

        protected function foobar()
        {
            if (42 === $this->foobar) {
                $this->foo();
            }

            return $this->foobar;
        }
    }

    class InheritClassTwo implements IndirectImplementedInterface
    {
        protected $sindelfingen;

        private $qafoo;

        public function bar()
        {
            return (time() % 42 === 0 ? 1 : 2);
        }

        public function foobarbaz()
        {
            return time();
        }
    }

    interface DirectImplementedInterface
    {
        public function foo($p0);
    }

    interface IndirectImplementedInterface extends IndirectImplementedInterfaceTwo
    {
        public function bar();
    }

    interface IndirectImplementedInterfaceTwo extends \Traversable
    {
        public function foobarbaz();
    }
}