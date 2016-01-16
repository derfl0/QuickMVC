<?php
// We will propably need a session
session_start();

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