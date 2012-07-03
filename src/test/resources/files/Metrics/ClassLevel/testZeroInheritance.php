<?php
namespace
{
    class DefaultClassLevelMetricSet
        implements \ClassLevel\InterfaceThree,
                   \ClassLevel\InterfaceFive
    {
        private $foo = 23;

        public $bar = 42;

        protected $baz = 3.14;

        public function foo()
        {
            return $this->bar;
        }

        protected function bar()
        {
            return $this->baz() * $this->baz;
        }

        private function baz()
        {
            return $this->foo;
        }
    }
}

namespace ClassLevel
{
    interface InterfaceOne
    {
    }

    interface InterfaceTwo extends InterfaceOne
    {
    }

    interface InterfaceThree extends InterfaceOne
    {
    }

    interface InterfaceFour extends InterfaceTwo, InterfaceFive
    {
    }

    interface InterfaceFive extends InterfaceSix
    {
    }

    interface InterfaceSix
    {
    }
}