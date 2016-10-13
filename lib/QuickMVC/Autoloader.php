<?php
namespace QuickMVC;

class Autoloader
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
        spl_autoload_register('\QuickMVC\Autoloader::load');
    }

    /**
     * Autoload a class
     * @param $class Classname
     */
    public static function load($class) {

        // Namespace escape
        $class = str_replace('\\', DIRECTORY_SEPARATOR, $class);

        foreach (self::$paths as $path) {
            if (file_exists($path . DIRECTORY_SEPARATOR . $class . '.php')) {
                require $path . DIRECTORY_SEPARATOR . $class . '.php';
                return;
            }
            if (file_exists($path . DIRECTORY_SEPARATOR . $class . '.class.php')) {
                require $path . DIRECTORY_SEPARATOR . $class . '.class.php';
                return;
            }
        }
    }
}