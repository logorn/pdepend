<?php
class testCaFromFunctionCountsTypeOnce extends Exception
{

}

/**
 * @throws testCaFromFunctionCountsTypeOnce
 *
 * @param testCaFromFunctionCountsTypeOnce $o
 * @return testCaFromFunctionCountsTypeOnce
 */
function foo(testCaFromFunctionCountsTypeOnce $o)
{
    if ($o instanceof testCaFromFunctionCountsTypeOnce) {
        throw new testCaFromFunctionCountsTypeOnce();
    }
    return new testCaFromFunctionCountsTypeOnce();
}

/**
 * @return void
 * @throws testCaFromFunctionCountsTypeOnce
 */
function bar(testCaFromFunctionCountsTypeOnce $o)
{
    if ($o instanceof testCaFromFunctionCountsTypeOnce) {
        throw new testCaFromFunctionCountsTypeOnce();
    }
}
