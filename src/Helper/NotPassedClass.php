<?php

namespace App\Helper;

class NotPassedClass
{
    const NOT_PASSED = "notPassed";

    function showConstant():mixed
    {
        echo self::NOT_PASSED . "\n";
    }
}
