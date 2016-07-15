<?php

/*
Copy this example file to a new file called 'config.php'
and replace with your own parameters
*/

/* LDAP connection parameters */
define("LDAP_ADDRESS", "ldap://exampleldap.company.com/");
define("LDAP_PORT", 389);
define("LDAP_BIND", "uid=somebinduser,dc=company,dc=com");
define("LDAP_BIND_PASSWORD", "somebindpassword");

/* LDAP group DN */
/* LDAP_BASE_GROUP should be groupOfUniqueNames in LDAP */
define("LDAP_BASE_GROUP", "ou=Groups,dc=company,dc=com");
define("LDAP_FILTER_GROUP", "cn=*");

/* LDAP users */
define("LDAP_BASE_INDIVIDUAL", "ou=Users,dc=company,dc=com");
define("LDAP_FILTER_INDIVIDUAL", "uid=");

/* Server interface for outgoing connections (used by iptables) */
define("SERVER_INTERFACE", "eth0");

/* OpenVPN management interface */
define("SERVER_MGMT_ADDRESS", "127.0.0.1");
define("SERVER_MGMT_PORT", "1337");