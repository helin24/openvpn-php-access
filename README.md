# openvpn-php-access

## Usage

#### Connection
- User connects using OpenVPN client
- Initial shell script (`client-connect.sh`) is run using `client-connect` option in server configuration
    - LDAP rules for user are collected
    - iptables rules are written to control user access
    - Routes are pushed to client

#### Disconection
- User disconnets using OpenVPN client
- Initial shell script (`client-disconnect.sh`) is run using `client-disconnect` option in server configuration
    - User's iptables rules are deleted