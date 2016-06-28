<?php
require_once('/etc/openvpn/openvpn-php-access/config.php');
// require_once(__DIR__ . '/../config.php');
require_once(__DIR__ . '/Address.php');

class LDAP {

    private static $ldap = null;
    protected $connection;
    protected $bind;
    protected $credentials;

    protected function __construct() {
        // singleton class
    }

    public static function obtain() {
        if (is_null(self::$ldap)) {
            self::$ldap = new LDAP();
        }
        return self::$ldap;
    }

    public function getUserRules($user) {
        $groupRules = $this->getGroupRules($user);
        $individualRules = $this->getIndividualRules($user);

        $rules = array_merge($groupRules, $individualRules);
        if (empty($rules)) {
            throw new \Exception("User $user has no access permissions");
        }
        return $this->consolidateRules($rules);
    }

    protected function connect() {
        if (is_null($this->connection) || is_null($this->bind)) {
            
            $this->cr = $this->getCredentials();
            $this->connection = ldap_connect($this->cr["address"], $this->cr["port"]);
            $this->bind = ldap_bind($this->connection, $this->cr["bind"], $this->cr["bindPassword"]);
        }

    }

    protected function getCredentials() {
        return [
            "address" => LDAP_ADDRESS,
            "port" => LDAP_PORT,
            "bind" => LDAP_BIND,
            "bindPassword" => LDAP_BIND_PASSWORD,
            "baseDNIndividual" => LDAP_BASE_INDIVIDUAL,
            "filterIndividual" => LDAP_FILTER_INDIVIDUAL,
            "baseDNGroup" => LDAP_BASE_GROUP,
            "filterGroup" => LDAP_FILTER_GROUP
        ];
    }

    protected function getGroupRules($user) {
        $this->connect();
        $userSearch = $this->cr["filterIndividual"] . $user;

        $results = ldap_search($this->connection, $this->cr["baseDNGroup"], $this->cr["filterGroup"]);

        $accessRules = [];
        $nextEntry = ldap_first_entry($this->connection, $results);
        do {
            $attributes = ldap_get_attributes($this->connection, $nextEntry);
            $access = False;
            if ($members = $attributes["uniqueMember"]) {
                foreach ($members as $member) {
                    if (mb_strpos($member, $userSearch) === 0) {
                        $access = True;
                        break;
                    }
                }
            }

            if ($access) {
                foreach ($attributes["accessTo"] as $key => $accessPoint) {
                    if ($key !== "count") {
                        $accessRules[$accessPoint] = 1;
                    }
                }
            }
        } while ($nextEntry = ldap_next_entry($this->connection, $nextEntry));
        
        return $accessRules;        
    }

    protected function getIndividualRules($user) {
        $this->connect();

        $resultResource = ldap_search($this->connection, $this->cr["baseDNIndividual"], $this->cr["filterIndividual"] . $user);
        $result = ldap_get_entries($this->connection, $resultResource);

        $accessRules = [];
    // If $result["count"] is 0, it means no users were returned.
    // If accessto does not exist as a key, it means the user does not have individual rules
        if ($result["count"] && array_key_exists("accessto", $result[0])) {
            foreach ($result[0]["accessto"] as $key => $accessPoint) {
                if ($key !== "count") {
                    $accessRules[$accessPoint] = 1;
                }
            }
        }

        return $accessRules;
    }

    protected function consolidateRules($rulesArray) {
        $addresses = [];
        foreach ($rulesArray as $ruleDN => $v) {
            $addresses = array_merge($addresses, $this->getNetwork($ruleDN));
        }
        
        return $addresses;
    }

    protected function getNetwork($ruleDN) {
        $this->connect();

        list($filter, $base) = explode(',', $ruleDN, 2);

        $ipResource = ldap_list($this->connection, $base, $filter, ["ipnetworknumber", "ipnetmasknumber"]);
        $ipResults = ldap_get_entries($this->connection, $ipResource);

        $ip = $ipResults[0]["ipnetworknumber"][0];
        $netmask = $ipResults[0]["ipnetmasknumber"][0];

        $protocolResource = ldap_list($this->connection, $ruleDN, "cn=*", ["ipserviceport", "ipserviceprotocol"], 0);
        $protocolResults = ldap_get_entries($this->connection, $protocolResource);

        $addresses = [];
        if ($protocolResults["count"] > 0) {
            foreach ($protocolResults as $key => $result) {
                if ($key === "count") {
                    continue;
                }

                $address = new Address($ruleDN);
                $address->ip = $ip;
                $address->netmask = $netmask;
                $address->protocol = $result["ipserviceprotocol"][0];
                $address->port = $result["ipserviceport"][0];

                $addresses[] = $address;
            }
        }
        else {
            $address = new Address($ruleDN);
            $address->ip = $ip;
            $address->netmask = $netmask;

            $addresses[] = $address;
        }

        return $addresses;
    }
}
