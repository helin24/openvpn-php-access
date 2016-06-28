<?php
require_once('models/ConnectionManager.php');

list($script, $tempfile, $action, $user, $userIP, $proto, $dev) = $argv;
// $argv[0] is script location
// $argv[1] is location of user tempfile that will be sent back to client
// $argv[2] is action of connect or disconnect
// $argv[3] is user's identifier
// $argv[4] is user's IP
// $argv[5] is protocol
// $argv[6] is interface

$connectionManager = new ConnectionManager($user, $userIP);

if ($action == 'connect') {
	try {
		$connectionManager->connect();
	}
	catch (\Exception $ex) {
		file_put_contents($tempfile, "disable");
		throw $ex;
	}
} 
elseif ($action == 'disconnect') {
    $connectionManager->disconnect();
}
else {
    echo "action must be 'connect' or 'disconnect'; specified \$action = $action\n";
}
