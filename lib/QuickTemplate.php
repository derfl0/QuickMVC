<?php

/**
 * Created by PhpStorm.
 * User: intelec
 * Date: 15.01.16
 * Time: 13:51
 */
class QuickTemplate
{
    private static $template = 'default';

    public static function setTemplate($template) {
        self::$template = $template;
    }

    public static function render() {
        global $controller;
        include QuickConfig::$rootpath.'/templates/'.self::$template.'.php';
    }
}