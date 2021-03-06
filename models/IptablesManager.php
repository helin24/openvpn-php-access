<?php 
require_once(__DIR__ . '/../config.php');

class IptablesManager {

    private $insertIndex = 1;
    protected $user;
    protected $userAddress;

    public function __construct($user, $userAddress) {
        $this->user = $user;
        $this->userAddress = $userAddress;
    }

    /**
     * Deletes iptables rules associated with the user
     * @return none
     */
    public function deleteRules() {
        // drop user's rules from POSTROUTING
        exec('sudo iptables --table nat -D' . $this->getPostroutingString($this->userAddress));

        // flush and delete the user's chain
        exec('sudo iptables --table nat --flush ' . $this->userAddress);
        exec('sudo iptables --table nat --delete-chain ' . $this->userAddress);
    }

    /**
     * Writes new rules to iptables permitting user to access allowed addresses
     * @param  Address[] $accessibleAddresses 
     * @return none
     */
    public function createRules($accessibleAddresses) {
        // Create the user's chain
        exec($this->getUserChainCommand());
        exec($this->getPostRoutingCommand());


        // Could be missing protocol and dport if a general rule
        // Add rule in POSTROUTING to use individual chain
        foreach ($accessibleAddresses as $destination) {
            $stmt = $this->getDestinationCommand($destination);
            $logStmt = $this->getDestinationLogString($destination);
            exec($stmt, $output, $returnVar);

            if ($returnVar === 0) {
                $logStmt .= " SUCCESS";
            }
            else {
                $logStmt .= " FAILURE";
            }

            print($logStmt . "\n");
        }
    }

    /**
     * Creates string for the command to add a new user's chain
     * @return String
     */
    public function getUserChainCommand() {
        return 'sudo iptables --table nat --new-chain ' . $this->userAddress;
    }

    /**
     * Creates string for the command to insert rule to POSTROUTING chain to redirect to user's chain
     * @return String
     */
    public function getPostRoutingCommand() {
        return 'sudo iptables --table nat --append ' . $this->getPostroutingString();
    }

    /**
     * Creates a string for the command to insert a rule for the user to access a destination
     * @param Address
     * @return String
     */
    public function getDestinationCommand($destination) {
        $stmt = 'sudo iptables --table nat --insert ' . $this->userAddress 
            . ' ' . $this->insertIndex 
            . ' --out-interface ' . SERVER_INTERFACE
            . ' --source ' . $this->userAddress . '/32'
            . ' --destination ' . $destination->ip . '/' . $destination->netmask;

        if ($destination->protocol) {
            $stmt .= ' --protocol ' . $destination->protocol
            . ' --destination-port ' . $destination->port;
        }

        $stmt .= ' --jump MASQUERADE';

        return $stmt;
    }

    /**
     * Creates a string for logging information about a user's access to destination
     * @param Address 
     * @return String
     */
    public function getDestinationLogString($destination) {
        $logStmt = "Granting access for $this->user to $destination->ip/$destination->netmask";

         if ($destination->protocol) {
            $logStmt .= " on $destination->protocol/$destination->port";
        }

        return $logStmt;
   }

    /**
     * Returns the last part of iptables rules that refer to a user's chain from the POSTROUTING chain
     * @return String
     */
    public function getPostroutingString() {
        return " POSTROUTING --source $this->userAddress/32 --jump $this->userAddress -m comment --comment $this->user";
    }

}
