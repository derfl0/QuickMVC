<?php

// Prepare autoloader
require 'lib/QuickAutoloader.php';
QuickAutoloader::addPath('lib');
QuickAutoloader::addPath('app/model');

// Store root in global
QuickConfig::$rootpath = __DIR__;

// Parse the query string
$args = explode('/',$_SERVER['QUERY_STRING']);

// Parse requested controller (or fallback to index)
if ($args[0]) {
    $route = array_shift($args);
} else {
    $route = 'index';
}
$controllerName = ucfirst($route).'Controller';

// Load controller
require "app/controller/$route.php";
$controller = new $controllerName;

// Parse requested action (or fallback to index)
if ($args[0]) {
    $action = array_shift($args);
} else {
    $action = 'index';
}

// Tell the controller the action
$controller->action = $action;

// Tell the controller the args
$controller->args = $args;

// Tell the controller the view
$controller->view['path'] =
$controller->view['name'] = $route.'/'.$action;

// Load the template
QuickTemplate::render();