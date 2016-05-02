<?php

class RoutesWriter {
    
    private static $routesWriter = null;
    protected $filename;

    protected function __construct() {
        // singleton class
    }

    public static function obtain() {
        if (is_null(self::$routesWriter)) {
            self::$routesWriter = new RoutesWriter();
        }
        return self::$routesWriter;
    }

    public function writeToFile($accessibleAddresses) {
        // open file
        
        foreach ($accessibleAddresses as $address) {
            $this->translateToNetmask($address);

            // then write to a file
        }

        // direct non-internal traffic outside of VPN
        // Push file to user (included in address objects)
    }

    protected function translateToNetmask($address) {
        // change CIDR notation to netmask
    }
}