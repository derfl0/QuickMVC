<?php

namespace QuickMVC;

class URL
{
    function __construct($link)
    {
        $this->link = $link;
    }

    public static function generate($link)
    {
        return URL . '/' . $link;
    }

    function __toString()
    {
        return self::generate($this->link);
    }
}