<?php

// Prepare autoloader
require 'lib/QuickAutoloader.php';
QuickAutoloader::addPath('lib');
QuickAutoloader::addPath('app/model');

// Store root in global
QuickConfig::$rootpath = __DIR__;

// Parse requested controller
$controller = QuickController::load($_SERVER['QUERY_STRING']);

// Load the template
QuickTemplate::render();