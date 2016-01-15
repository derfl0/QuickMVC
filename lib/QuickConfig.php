<?php

class QuickConfig
{
    /**
     * @var Rootpath of the application
     */
    public static $rootpath;

    /**
     * @var Root of the views
     */
    public static $viewpath = 'app/view/';

    public static function getViewPath() {
        return self::$rootpath.'/'.self::$viewpath;
    }
}