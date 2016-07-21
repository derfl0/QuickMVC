<?php

namespace QuickMVC;

class Navigation
{
    private static $navigation;
    private static $active = false;
    private $_subnavigation = array();
    private $_link;
    private $_text;

    public static function get()
    {
        if (!self::$navigation) {
            self::$navigation = new self;
        }
        return self::$navigation;
    }

    public function add($link, $text, $name = null)
    {
        $nav = new self();
        $nav->set($link, $text);
        $this->_subnavigation[$name] = $nav;

        // Set member var
        if ($name) {
            $this->$name = $this->_subnavigation[$name];
        }
    }

    public function set($link, $text)
    {
        $this->_link = $link;
        $this->_text = $text;
    }

    public function addRoute($route, $text, $name = null)
    {
        $this->add(QuickURL::generate($route), $text, $name);
    }

    public function render()
    {
        if ($this->_link) {
            $response = "<li><a href='{$this->_link}'>{$this->_text}</a></li>";
        }
        if ($this->_subnavigation) {
            $response .= "<ul>";
            foreach ($this->_subnavigation as $sub) {
                $response .= $sub->render();
            }
            $response .= "</ul>";
        }
        return $response;
    }
}