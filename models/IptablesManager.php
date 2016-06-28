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
        // get all POSTROUTING rules
        $rules = [];
        exec('sudo iptables --table nat -L POSTROUTING', $rules);
        $rules = array_slice($rules, 2, count($rules) - 2);

        // find indices of rules related to user
        $userRuleIndices = [];

        for ($index = 1; $index <= count($rules); $index++) {
            if (is_numeric(mb_strpos($rules[$index - 1], $userAddress))) {
                $userRuleIndices[] = $index;
            }
        }

        if (count($userRuleIndices) > 0) {
        // drop user's rules from POSTROUTING
             foreach (array_reverse($userRuleIndices) as $index) {
                 exec('sudo iptables --table nat -D POSTROUTING ' . $index);
             }
    
             // flush and delete the user's chain
             exec('sudo iptables --table nat --flush ' . $userAddress);
             exec('sudo iptables --table nat --delete-chain ' . $userAddress);
        }
    }

    public static function createRules($userAddress, $accessibleAddresses) {
        // first drop all user's rules
        self::deleteRules($userAddress);
        self::setLastForwardIndex();

        // Create the user's chain
        exec('sudo iptables --table nat --new-chain ' . $userAddress);
        exec('sudo iptables --table nat --append POSTROUTING --source ' . $userAddress . '/32 --jump ' . $userAddress);

        // Add rule in POSTROUTING to use individual chain
        foreach ($accessibleAddresses as $destination) {
            $stmt = 'sudo iptables --table nat --insert ' . $userAddress 
                . ' ' . self::$insertIndex 
                . ' --out-interface ' . SERVER_INTERFACE
                . ' --source ' . $userAddress . '/32'
                . ' --destination ' . $destination->ip . '/' . $destination->netmask
                . ' --protocol ' . $destination->protocol
                . ' --destination-port ' . $destination->port
                . ' --jump MASQUERADE';
            exec($stmt);
        }
    }

}
