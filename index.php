<?php
// Set mode to development
define('DEV', true);

// Set error mode
if (DEV) {
    error_reporting(E_ALL & ~E_NOTICE);
    ini_set("display_errors", 1);

} else {
    error_reporting(0);
}

// We will propably need a session
session_start();

// Prepare autoloader
require 'lib/QuickAutoloader.php';
QuickAutoloader::addPath('lib');
QuickAutoloader::addPath('app/model');

// Define constants
define('URL', substr($_SERVER['SCRIPT_NAME'], 0, -10));
define('PATH', __DIR__);
define('APP', PATH . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR);
define('VIEWS', APP . 'views' . DIRECTORY_SEPARATOR);
define('CONTROLLERS', APP . 'controllers' . DIRECTORY_SEPARATOR);
define('MODELS', APP . 'models' . DIRECTORY_SEPARATOR);

// Restore DB Dump if in development mode
if (DEV) {
    QuickDB::restoreDump();
}

// Parse requested controller and ignore params
$controller = QuickController::load(current(explode('&', $_SERVER['QUERY_STRING'])));

// And here goes the output magic ;)
echo $controller->render();

// If we are in developmode dump our complete db
if (DEV) {
    QuickDB::storeDump();
}