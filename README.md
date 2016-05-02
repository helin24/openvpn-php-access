# openvpn-php-access

### Plan
- Client Connects

- Shell script is called using `client-connect` in server.conf 

- Shell script calls PHP script with command (to connect or disconnect) and passes arguments (most likely just the temporary file with environment variables)

- PHP script links to main connection manager class
    - connection manager class has methods connect, disconnect
        - connect requires other classes
            - access LDAP and get user access rules
                - construct with a username
                - method to look in groups
                - method to look in users
                - method to consolidate rules (check for duplicates)
                - method to get all addresses associated with rules - return array of addresses to connection manager class
                - consider how to implement least access if desired, potentially need to ignore least access if admin user, etc.
                - start with most access and try to make this changeable 
            - change access rules into iptables rules
                - construct with user's IP
                - drop chains
                - method to take a single address object and put a rule into iptables
            - a class that puts routes into a file
                - construct with the filename
                - translate cidr(bitmask) notation to netmask [link](https://oav.net/mirrors/cidr.html)
                - write to file
        - disconnect uses iptables class
            - drop chains
            - use a database to record traffic data

### Design pattern
- Command pattern?
    - LDAP server is 'client' that issues command
    - Command object is address/rule object (actually an interface for the two receivers to use)
    - Invoker is connection manager, which takes command and passes it to executors
    - Two receivers - iptables generator and the class that puts routes into a file
        - Both have an execute method that uses address/rule object but does different things with it


### Questions
- iptables commands and what happens in line 16 of netfilter_openvpn.sh [link](http://www.howtogeek.com/177621/the-beginners-guide-to-iptables-the-linux-firewall/)
    - does iptables assume all ports if no port specified