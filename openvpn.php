<?php
require_once('models/ConnectionManager.php');

list($script, $action, $user) = $argv;
// $argv[0] is script location
// $argv[1] is action of connect or disconnect
// $argv[2] is user's identifier

$connectionManager = new ConnectionManager($user);

if ($action == 'connect') {
    $connectionManager->connect();
} 
elseif ($action == 'disconnect') {
    $connectionManager->disconnect();
}
else {
    echo "action must be 'connect' or 'disconnect'; specified \$action = $action\n";
}