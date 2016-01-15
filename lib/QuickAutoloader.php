<?php

class QuickAutoloader
{
    private static $paths = array();
    private static $registered;

    /**
     * Register path to the autoloader
     *
     * @param $path path for autoloading
     */
    public static function addPath($path)
    {
        self::$paths[] = $path;

        // Check if we are already registered
        if (!self::$registered) {
            self::register();
        }
    }

    /**
     * Register myself
     */
    private static function register()
    {
        spl_autoload_register('QuickAutoloader::load');
    }

    /**
     * Autoload a class
     * @param $class Classname
     */
    public static function load($class) {
        foreach (self::$paths as $path) {
            if (file_exists($path . '/' . $class . '.php')) {
                require $path . '/' . $class . '.php';
                return;
            }
            if (file_exists($path . '/' . $class . '.class.php')) {
                require $path . '/' . $class . '.class.php';
                return;
            }
        }
    }
}