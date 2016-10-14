<?php

/**
 * Fallback controller if nothing is found
 */
class IndexController extends \QuickMVC\Controller
{
    public function _index($what = "Fallback") {
        $this->something = $what;

        $user = User::find(2);
        var_dump($user);die;
    }

    public function _redirectme() {
        self::redirect('You just got redirected');
    }

}