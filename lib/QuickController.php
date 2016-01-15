<?php

/**
 * QuickController
 */
class QuickController
{

    function before() {

    }

    function after() {

    }

    function index() {
        $this->test = "Testing";
    }

    function render() {
        $this->before();
        call_user_func_array(array($this, $this->action), $this->args);
        // Dereference this object
        foreach(get_object_vars($this) as $var => $value) {
            $$var = $value;
        }
        //ob_start();
        include QuickConfig::getViewPath().$this->view['name'].'.php';
        $this->after();
    }
}