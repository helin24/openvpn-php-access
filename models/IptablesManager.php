<?php 
require_once(__DIR__ . '/../config.php');

class IptablesManager {

    private $insertIndex = 3;
    protected $user;
    protected $userAddress;

    public function __construct($user, $userAddress) {
        $this->user = $user;
        $this->userAddress = $userAddress;
    }

    public function deleteRules() {
        // drop user's rules from POSTROUTING
        exec('sudo iptables --table nat -D' . $this->getPostroutingString($this->userAddress));

        // flush and delete the user's chain
        exec('sudo iptables --table nat --flush ' . $this->userAddress);
        exec('sudo iptables --table nat --delete-chain ' . $this->userAddress);
    }

    public function createRules($accessibleAddresses) {
        // first drop all user's rules
        $this->deleteRules();

        // Create the user's chain
        exec('sudo iptables --table nat --new-chain ' . $this->userAddress);
        exec('sudo iptables --table nat --append ' . $this->getPostroutingString());

        // Additional default rules for user's chain
        exec("sudo iptables --table nat --append " . $this->userAddress . " --match conntrack --ctstate ESTABLISHED --jump ACCEPT" . $this->getUserComment());
        exec("sudo iptables --table nat --append " . $this->userAddress . " --jump LOG --log-prefix \"DROP $this->user\"" . $this->getUserComment());
        exec("sudo iptables --table nat --append " . $this->userAddress . " --jump DROP" . $this->getUserComment());

        // Could be missing protocol and dport if a general rule
        // Add rule in POSTROUTING to use individual chain
        foreach ($accessibleAddresses as $destination) {
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
            exec($stmt);
        }
    }

    public function getPostroutingString() {
        return " POSTROUTING --source $this->userAddress/32 --jump $this->userAddress";
    }

    public function getUserComment() {
        return " --match comment --comment \"$this->user at $this->userAddress\"";
    }

}
