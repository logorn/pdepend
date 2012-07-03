<?php
namespace {

    class ClassLevelOneLevelInherit
        extends \ClassLevelNsOne\InheritClass
        implements \ClassLevelNsTwo\DirectImplementedInterface
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

    class InheritClass implements \ClassLevelNsTwo\IndirectImplementedInterface
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
}

namespace ClassLevelNsTwo {

    interface DirectImplementedInterface extends IndirectImplementedInterfaceTwo
    {
        public function foo($p0);
    }

    interface IndirectImplementedInterface
    {
        public function bar();
    }

    interface IndirectImplementedInterfaceTwo
    {

    }
}