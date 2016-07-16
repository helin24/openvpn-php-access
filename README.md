# openvpn-php-access

A set of PHP scripts to use for OpenVPN authorization with an LDAP backend. The following features are available:

 - Access rules pulled from an LDAP backend
 - Server-side implementation of access rules via IPTables
 - Client-side implementation of access rules via `client-connect` script, where routes are pushed dynamically; no need for static definitions in CCD config directory
 - IPv4 routing through the OpenVPN server using `MASQUERADE` rules
 - No need to route everything through OpenVPN since dynamic routes are pushed to user based on their access rules
 - Resolution of DNS names, if needed, by placing FQDNs in `ipNetworkNumber` in LDAP.

## Installation

Place the `openvpn-php-access` folder in your OpenVPN folder.

```
cd /etc/openvpn
git clone https://github.com/helin24/openvpn-php-access.git
```

Copy the `config-example.php` file to `config.php`. Make changes to `config.php` as needed for your setup.

```
cd openvpn-php-access/
cp config-example.php config.php
```

Add the following line to your OpenVPN configuration file:

```
client-connect /etc/openvpn/openvpn-php-access/client-connect.sh
client-disconnect /etc/openvpn/openvpn-php-access/client-disconnect.sh
management 127.0.0.1 <random-port>
```

Install the following PHP dependencies:

```
sudo apt-add-repository ppa:ondrej/php
sudo apt-get -y update
sudo apt-get install php5.6-cli php5.6-ldap php5.6-mbstring
```

## Script Logic

### Connection

The script follows the following logic after the user has successfully authenticated:

 - User connects using OpenVPN client.
 - Initial shell script (`client-connect.sh`) is run using `client-connect` option in server configuration.
 - LDAP rules for user are collected.
  - Script first checks for user as `uniqueMember`in Group definitions found in `config.php` file. The user can be a member of multiple groups.
  - Script then checks for per user rules by looking for `accessTo` entries in the user's DN.
  - IP addresses and ports are determined from access rules, and finally, duplicates are removed.
 - IPTables rules are written to control user access on the server side.
 - Routes are pushed to client dynamically on each connect.

### Disconnection

The script follows the following logic when the user initiates a disconnect:

- User disconnects using OpenVPN client.
- Initial shell script (`client-disconnect.sh`) is run using `client-disconnect` option in server configuration.
- User's IPTables rules are deleted.

## Documentation

See the following wiki articles for more detailed information on the LDAP and OpenVPN setup:

- [LDAP Structure](https://github.com/helin24/openvpn-php-access/wiki/LDAP-Structure "LDAP Structure")
