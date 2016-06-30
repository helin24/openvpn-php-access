#!/bin/bash

#======================================================================================================================
# vim: softtabstop=4 shiftwidth=4 expandtab fenc=utf-8 spell spelllang=en cc=120
#======================================================================================================================
#
#          FILE: client-disconnect.sh
#
#   DESCRIPTION: Shell executable used during client disconnect; parameters passed to openvpn.php
#
#          BUGS: https://github.com/helin24/openvpn-php-access/issues
#
#       LICENSE: GNU GENERAL PUBLIC LICENSE Version 3, 29 June 2007
#  ORGANIZATION: Helin Shiah
#       CREATED: Mon May 2 09:38:36 PDT 2016
#======================================================================================================================

## Parameters
echo "Starting disconnect" >> test-log.txt

PHPCLI=$(which php)
SUDO=$(which sudo)

## Sanity check

if [[ -z "${PHPCLI}" || "${PHPCLI}" = "" ]]; then echo "ERROR: PHP NOT INSTALLED" && exit 1 ; fi
if [[ -z "${SUDO}" || "${SUDO}" = ""  ]]; then echo "ERROR: SUDO NOT FOUND" && exit 1 ; fi

## Black magic
printenv >> test-log.txt
echo "Disconnect script" >> test-log.txt
${SUDO} --preserve-env ${PHPCLI} $(pwd)/openvpn-php-access/openvpn.php disconnect
