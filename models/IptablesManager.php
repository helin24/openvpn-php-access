<?php 

// Not sure how PHP interfaces with iptables - likely a lot of exec() statements?
class IptablesManager {

    private static $iptables = null;

    protected function __construct() {
        // singleton class
    }

    public static function obtain() {
        if (is_null(self::$iptables)) {
            self::$iptables = new IptablesManager();
        }
        return self::$iptables;
    }

    public function deleteRules($userAddress) {
        // drop rules from table
    }

    public function createRules($userAddress, $accessibleAddresses) {
        // first drop all user's rules
        $this->deleteRules($userAddress);

        // translate accessible addresses to rules and insert into table
    }

}