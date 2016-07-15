<?php

class Address  {

    public $dn;
    public $ip;
    public $netmask;
    public $port;
    public $protocol;

    public function __construct($dn, $ip, $netmask) {
        $this->dn = $dn;
        $this->ip = $ip;
        $this->netmask = $netmask;
    }
    
    /**
     * Translates netmask to dotted decimal notation 
     * @return String
     */
    public function getDecimalNetMask() {
        $converter = [
            "0" => "0.0.0.0",
            "1" => "128.0.0.0",
            "2" => "192.0.0.0",
            "3" => "224.0.0.0",
            "4" => "240.0.0.0",
            "5" => "248.0.0.0",
            "6" => "252.0.0.0",
            "7" => "254.0.0.0",
            "8" => "255.0.0.0",
            "9" => "255.128.0.0",
            "10" => "255.192.0.0",
            "11" => "255.224.0.0",
            "12" => "255.240.0.0",
            "13" => "255.248.0.0",
            "14" => "255.252.0.0",
            "15" => "255.254.0.0",
            "16" => "255.255.0.0",
            "17" => "255.255.128.0",
            "18" => "255.255.192.0",
            "19" => "255.255.224.0",
            "20" => "255.255.240.0",
            "21" => "255.255.248.0",
            "22" => "255.255.252.0",
            "23" => "255.255.254.0",
            "24" => "255.255.255.0",
            "25" => "255.255.255.128",
            "26" => "255.255.255.192",
            "27" => "255.255.255.224",
            "28" => "255.255.255.240",
            "29" => "255.255.255.248",
            "30" => "255.255.255.252",
            "31" => "255.255.255.254",
            "32" => "255.255.255.255"];
        return $converter[$this->netmask];
    }

    /**
     * Creates string to display all information about this address
     * @return String
     */
    public function display() {
        $str = "$this->ip/$this->netmask";
        if ($this->protocol) {
            $str .= " on $this->protocol/$this->port";
        }
        $str .= " ($this->dn)";
        return $str;
    }

    /**
     * Creates one or multiple address objects depending on whether IPs need to be resolved
     * @param  String $dn Distinct name identifier
     * @param  String $ipNetworkNumber
     * @param  String $netmask
     * @param  String $port
     * @param  String $protocol
     * @return Address[]
     */
    public static function create($dn, $ipNetworkNumber, $netmask, $port = null, $protocol = null) {
        $actualIps = self::getUsableIps($ipNetworkNumber);

        $addresses = [];

        foreach ($actualIps as $ip) {
            $address = new Address($dn, $ip, $netmask);

            if ($port) {
                $address->port = $port;
            }

            if ($protocol) {
                $address->protocol = $protocol;
            }

            $addresses[] = $address;
        }

        return $addresses;
    }

    /**
     * Checks whether to resolve IP using DNS and returns all applicable IPs
     * @param  String $ip
     * @return String[]
     */
    public function getUsableIps($ip) {

        $ipv4Matcher = "#^(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$#";
        $ipv6Matcher = "#^(?:[A-F0-9]{1,4}:){7}[A-F0-9]{1,4}$#";

        if (preg_match($ipv4Matcher, $ip)) {
            return [$ip];
        }
        else if (preg_match($ipv6Matcher, $ip)) {
            return [$ip];
        }
        else {
            $results = dns_get_record($ip, DNS_A);
            foreach ($results as $result) {
                $ips[] = $result["ip"];
            }
            return $ips;
        }
    }
}
