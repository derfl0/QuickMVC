<?php

namespace QuickMVC;

class Request
{
    public static function get($var) {
        return $_REQUEST[$var];
    }
}