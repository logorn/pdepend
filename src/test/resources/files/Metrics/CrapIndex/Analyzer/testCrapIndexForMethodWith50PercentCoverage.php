<?php
class testCrapIndexForMethodWith50PercentCoverage
{
    function foo()
    {
        $x = (true ? ($y ? 42 : ($z || $foo ? ($a && $b) : 17)) : 23);
    }
}