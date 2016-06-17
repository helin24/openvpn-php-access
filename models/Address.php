<?php

class Address  {

    public $dn;
    public $ip;
    public $netmask;
    public $port;
    public $protocol;

    public function __construct($dn) {
        $this->dn = $dn;
    }
}