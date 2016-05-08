<?php 
require_once(__DIR__ . '/../config.php');

class IptablesManager {

    private static $iptables = null;
    private static $insertIndex = 1;

    public static function setLastForwardIndex() {
        // find what index to put new forward rules in? If we use a drop statement at the very bottom of the rules and don't want all new statements to go in at the top?
        self::$insertIndex = 1;
    }

    public static function deleteRules($userAddress) {
        // find all forward rules from table
        $rules = exec('sudo iptables -L FORWARD');

        // find indices of rules with userAddress
        $userRuleIndices = [];

        for ($index = 1; $index <= count($rules); $index++) {
            if (mb_strpos($rules[$index], $userAddress)) {
                $userRuleIndices[] = $index;
            }
        }

        // drop user's rules from table
        foreach (array_reverse($userRuleIndices) as $index) {
            exec('sudo iptables -D FORWARD ' . $index);
        }
    }

    public static function createRules($userAddress, $accessibleAddresses) {
        // first drop all user's rules
        self::deleteRules($userAddress);
        self::setLastForwardIndex();

        var_dump($userAddress);
        foreach ($accessibleAddresses as $destination) {
            $stmt = 'sudo iptables -I FORWARD ' . $insertIndex 
                . ' -i ' . SERVER_INTERFACE
                . ' -s ' . $userAddress
                . ' -d ' . $destination
                . '-j ACCEPT';
            exec($stmt);
        }
    }

}