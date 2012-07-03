<?php
namespace
{
    class DefaultClassLevelMetricSet
        implements \ClassLevel\InterfaceThree,
                   \ClassLevel\InterfaceFive
    {

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