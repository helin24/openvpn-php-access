<?php

class RoutesWriter {
    
    protected $filename;

    public function __construct($filename) {
    	$this->filename = $filename;
    }

    public function writeToFile($accessibleAddresses) {
        $file = fopen($this->filename, 'w');
        
        foreach ($accessibleAddresses as $address) {
            $netmask = $address->getDecimalNetmask();
            fwrite($file, "push \"route $address->ip $netmask\"\n");
        }

        return fclose($file);
    }
}
