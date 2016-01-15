<?php

/**
 * QuickController
 */
class QuickController
{

    function before()
    {

    }

    function after()
    {

    }

    function index()
    {
        $this->test = "Testing";
    }

    function render()
    {
        $this->before();
        call_user_func_array(array($this, $this->action), $this->args);
        // Dereference this object
        foreach (get_object_vars($this) as $var => $value) {
            $$var = $value;
        }
        //ob_start();
        include QuickConfig::getViewPath() . $this->view['name'] . '.php';
        $this->after();
    }

    public static function load($route)
    {
        // Parse the query string
        $args = explode('/', $route);

        // Parse requested controller (or fallback to index)
        if ($args[0] && file_exists($args[0])) {
            $route = array_shift($args);
        } else {
            $route = 'index';
        }
        $controllerName = ucfirst($route) . 'Controller';

        // Load controller
        require "app/controller/$route.php";
        $controller = new $controllerName;

        // Parse requested action (or fallback to index)
        if ($args[0] && method_exists($controller, $args[0])) {
            $action = array_shift($args);
        } else {
            $action = 'index';
        }

        // Tell the controller the action
        $controller->action = $action;

        // Tell the controller the args
        $controller->args = $args;

        // Tell the controller the view
        $controller->view['path'] = QuickConfig::getViewPath();
        $controller->view['name'] = $route.'/'.$action;

        return $controller;
    }
}