<?php

class QuickTemplate
{
    private static $template = 'default';
    private static $scripts = array();
    private static $style = array();

    public static function setTemplate($template) {
        self::$template = $template;
    }
    public static function render() {
        global $controller;
        include QuickConfig::$rootpath.'/templates/'.self::$template.'.php';
    }

    public static function addScript($script) {
        self::$scripts[] = $script;
    }

    public static function addStyle($style) {
        self::$style[] = $style;
    }

    public static function getHead() {

        // Add all stylesheets
        foreach (self::$styles as $style) {
            $head .= '<link rel="stylesheet" href="'.$style.'">';
        }

        // Add all scripts
        foreach (self::$scripts as $script) {
            $head .= ' <script src="'.$script.'"></script> ';
        }

        return $head;
    }
}