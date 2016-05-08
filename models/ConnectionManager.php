<?php
require_once('LDAP.php');
require_once('IptablesManager.php');
require_once('RoutesWriter.php');

class ConnectionManager {

    public $user;

    public function __construct($user){
        $this->user = $user;
    }

    public function connect() {
        // Get rules from LDAP
        $rules = LDAP::obtain()->getUserRules($this->user);

        // Pass rules object to iptables
        IptablesManager::createRules($this->user, $rules);

        // Pass rules object to routes file generator
        RoutesWriter::obtain()->writeToFile($rules);
    }

    public function disconnect() {

        IptablesManager::deleteRules($userAddress);
    }
}