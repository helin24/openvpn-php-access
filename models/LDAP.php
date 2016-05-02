<?php
require_once(__DIR__ . '/../config.php');

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
        var_dump($rules);
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
        if ($result["count"]) {

            foreach ($result[0]["accessto"] as $key => $accessPoint) {
                if ($key !== "count") {
                    $accessRules[$accessPoint] = 1;
                }
            }
        }

        return $accessRules;
        // Or potentially return false if user not found?
    }

    protected function consolidateRules($rulesArray) {
        $uniqueRules = array_unique($rulesArray);

        $addresses = [];
        foreach ($uniqueRules as $ruleDN) {
            $addresses[] = $this->getNetwork($ruleDN);
        }

        return $addresses;
    }

    protected function getNetwork($ruleDN) {
        $this->connect();
        // Retrieve information from LDAP
        // Make new address object
    }
}