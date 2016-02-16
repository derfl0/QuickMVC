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

// Store root in global
QuickConfig::$rootpath = __DIR__;
QuickConfig::$rooturl = substr($_SERVER['SCRIPT_NAME'], 0, -10);

// Restore DB Dump if in development mode
if (DEV) {
    QuickDB::restoreDump();
}

// Parse requested controller and ignore params
$controller = QuickController::load(current(explode('&', $_SERVER['QUERY_STRING'])));

// Load the template
ob_start();
QuickTemplate::render();
ob_end_flush();

// If we are in developmode dump our complete db
if (DEV) {
    QuickDB::storeDump();
}