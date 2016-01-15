<?php

/**
 * Fallback controller if nothing is found
 */
class IndexController extends QuickController
{
    public function index($what) {
        $this->something = $what ? : 'Nothing';
    }
    
}