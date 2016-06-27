<?php
require_once('models/ConnectionManager.php');

list($script, $tempfile, $action, $user) = $argv;
// $argv[0] is script location
// $argv[1] is location of user tempfile that will be sent back to client
// $argv[2] is action of connect or disconnect
// $argv[3] is user's identifier

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
