<?php

/**
 * Fallback controller if nothing is found
 */
class IndexController extends QuickController
{
    public function _index($what = "Fallback") {
        $this->something = $what;
    }

    public function _redirectme() {
        self::redirect('You just got redirected');
    }

}