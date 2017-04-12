<?php
/**
 * ###INSTALL###
 *
 * Rename QuickConfig.dist.php to QuickConfig!
 */
require "config/QuickConfig.php";

// Load autoloader
require 'lib'.DIRECTORY_SEPARATOR.'quickmvc'.DIRECTORY_SEPARATOR.'Autoloader.php';

// Set error mode
if (\QuickMVC\Config::DEVELOPMENT_MODE) {
    error_reporting(E_ALL & ~E_NOTICE);
    ini_set("display_errors", 1);
} else {
    error_reporting(0);
}

// We will propably need a session
session_start();

// Prepare autoloader
\QuickMVC\Autoloader::addPath('lib');
\QuickMVC\Autoloader::addPath('app/models');

// Define constants
define('URL', substr($_SERVER['SCRIPT_NAME'], 0, -10));
define('PATH', __DIR__);
define('APP', PATH . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR);
define('VIEWS', APP . 'views' . DIRECTORY_SEPARATOR);
define('CONTROLLERS', APP . 'controllers' . DIRECTORY_SEPARATOR);
define('MODELS', APP . 'models' . DIRECTORY_SEPARATOR);

\QuickMVC\Migrator::migrate();

// Parse requested controller and ignore params
$controller = \QuickMVC\Controller::load($_REQUEST['_quickmvc']['route']);

// And here goes the output magic ;)
echo $controller->render();

