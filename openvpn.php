<?php
require_once('models/ConnectionManager.php');

$action = $argv[1];
$user = getenv('username');
$userIP = getenv('ifconfig_pool_remote_ip');

if ($action == 'connect') {
    $tempfile = $argv[2];
    $proto = getenv('proto_1');

    $connectionManager = new ConnectionManager($user, $userIP, $tempfile);

    try {
        $connectionManager->connect();
    }
    catch (\Exception $ex) {
        $connectionManager->forceDisconnect($ex);
    }
} 
elseif ($action == 'disconnect') {
    $connectionManager = new ConnectionManager($user, $userIP);
    $connectionManager->disconnect();
}
else {
    echo "action must be 'connect' or 'disconnect'; specified \$action = $action\n";
}
