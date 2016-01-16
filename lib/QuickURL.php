<?php

class QuickURL
{
    function __construct($link) {
        $this->link = $link;
    }

    public static function generate($link) {
        return QuickConfig::$rooturl.'/'.$link;
    }

    function __toString() {
        return self::generate($this->link);
    }
}