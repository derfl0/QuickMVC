<?php

class QuickConfig
{
    const REDIRECT_MAX = 10;

    /**
     * @var Rootpath of the application
     */
    public static $rootpath;

    /**
     * @var Root of the views
     */
    public static $viewpath = 'app/view/';

    /**
     * @return string Path to views
     */
    public static function getViewPath() {
        return self::$rootpath.'/'.self::$viewpath;
    }

    /**
     * @var Root of the controllers
     */
    public static $controllerpath = 'app/controller/';

    /**
     * @return string Path to controller
     */
    public static function getControllerPath() {
        return self::$rootpath.'/'.self::$controllerpath;
    }
}