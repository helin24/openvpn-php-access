<?php

class Address  {

    public $user;
    public $dn;
    public $port;
    public $protocol;

    public function __construct($user) {
        $this->user = $user;
    }
}