<?php

/**
 * Fallback controller if nothing is found
 */
class IndexController extends QuickController
{
    public function index($what) {
        $this->something = $what ? : 'Nothing';
    }

    public function redirectme() {
        self::redirect('You just got redirected');
    }

}