<?php

namespace QuickMVC;

class Template
{
    private $template;
    private $scripts = array();
    private $styles = array();

    public function __construct($template = 'default') {
        $this->template = $template;
    }

    public static function setTemplate($template)
    {
        self::$template = $template;
    }

    public function render($body)
    {
        ob_start();
        include PATH . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . $this->template . '.php';
        $render = ob_get_contents();
        ob_end_clean();
        return $render;
    }

    public function addScript($script)
    {
        $this->scripts[] = $script;
    }

    public function addStyle($style)
    {
        $this->styles[] = $style;
    }

    public function getHead()
    {
        $head = '';

        // Add all stylesheets
        foreach ($this->styles as $style) {
            $head .= '<link rel="stylesheet" href="' . $style . '">';
        }

        // Add all scripts
        foreach ($this->scripts as $script) {
            $head .= ' <script src="' . $script . '"></script> ';
        }

        return $head;
    }
}