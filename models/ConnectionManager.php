<?php
require_once('LDAP.php');
require_once('IptablesManager.php');
require_once('RoutesWriter.php');

class ConnectionManager {

    public $user;
    public $userIP;
    public $iptables;
    public $routesWriter;

    public function __construct($user, $userIP, $file = null) {
        $this->user = $user;
        $this->userIP = $userIP;
        $this->iptables = new IptablesManager($user, $userIP);
        if ($file) {
            $this->routesWriter = new RoutesWriter($file);
        }
    }

    /**
     * Retrieve user permissions from LDAP and allow users to access servers based on permissions
     * @return none
     */
    public function connect() {
        // Get rules from LDAP
        $rules = LDAP::obtain()->getUserRules($this->user);

        // Pass rules object to iptables
        $this->iptables->createRules($rules);

        // Pass rules object to routes file generator
        $this->routesWriter->writeToFile($rules);
    }

    /**
     * Remove user permissions
     * @return none
     */
    public function disconnect() {
        $this->iptables->deleteRules();
    }

    /**
     * Send a message why a user is disconnected and disable their connection
     * @param  Object $exception Optional Exception
     * @return none
     */
    public function forceDisconnect($exception = null) {
        echo($exception->getMessage());
        echo($exception->getTraceAsString());
        $this->routesWriter->writeDisable();
    }
}
