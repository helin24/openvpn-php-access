<?php
require_once('LDAP.php');
require_once('IptablesManager.php');
require_once('RoutesWriter.php');

class ConnectionManager {

    public $user;
    public $userIP;
    public $file;
    public $iptables;

    public function __construct($user, $userIP, $file = null) {
        $this->user = $user;
        $this->userIP = $userIP;
        $this->file = $file;
        $this->iptables = new IptablesManager($user, $userIP);
    }

    public function connect() {
        // Get rules from LDAP
        $rules = LDAP::obtain()->getUserRules($this->user);

        // Pass rules object to iptables
        $this->iptables->createRules($rules);

        // Pass rules object to routes file generator
        $routesWriter = new RoutesWriter($this->file);
        $routesWriter->writeToFile($rules);
    }

    public function disconnect() {
        $this->iptables->deleteRules();
    }
}
