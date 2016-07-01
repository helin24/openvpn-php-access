<?php

class RoutesWriter {
    
    protected $filename;

    public function __construct($filename) {
    	$this->filename = $filename;
    }

    /**
     * Write allowed routes to file (which pushes routes to client)
     * @param  Object[] $accessibleAddresses Array of Address objects that client is allowed to access
     * @return Boolean True if file closure is successful, false otherwise
     */
    public function writeToFile($accessibleAddresses) {
        $file = fopen($this->filename, 'w');
        
        foreach ($accessibleAddresses as $address) {
            $netmask = $address->getDecimalNetmask();
            fwrite($file, "push \"route $address->ip $netmask\"\n");
        }

        return fclose($file);
    }

    /**
     * Writes 'disable' to a file (which causes client to disconnect)
     * @return Int|Boolean Number of bytes if write is successful, false otherwise
     */
    public function writeDisable() {
        return file_put_contents($this->filename, "disable");
    }
}
