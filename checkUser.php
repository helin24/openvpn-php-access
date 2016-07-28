<?php
require_once('models/LDAP.php');
require_once('models/Address.php');
require_once('models/IptablesManager.php');

$user = $argv[1];
$iptables = new IptablesManager($user, "<userIP>");
$rules = LDAP::obtain()->getUserRules($user);

$commandStr = $iptables->getUserChainCommand()
    . "\n"
    . $iptables->getPostroutingCommand()
    . "\n";

foreach ($rules as $rule) {
    print_r($rule->display() . "\n");
    $commandStr .= $iptables->getDestinationCommand($rule) . "\n";
}

print_r("iptables rules:\n");
print_r($commandStr);
