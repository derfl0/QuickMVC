<?php
// Set mode to development
define('DEV', true);

// Set error mode
if (DEV) {
    error_reporting(E_ALL & ~E_NOTICE);
    ini_set("display_errors", 1);
    set_error_handler("var_dump");
} else {
    error_reporting(0);
}

// We will propably need a session
session_start();

// Set error reporting

// Prepare autoloader
require 'lib/QuickAutoloader.php';
QuickAutoloader::addPath('lib');
QuickAutoloader::addPath('app/model');

// Store root in global
QuickConfig::$rootpath = __DIR__;
QuickConfig::$rooturl = substr($_SERVER['SCRIPT_NAME'], 0, -10);

// Parse requested controller and ignore params
$controller = QuickController::load(current(explode('&', $_SERVER['QUERY_STRING'])));

// Load the template
QuickTemplate::render();