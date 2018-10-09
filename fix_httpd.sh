#!/bin/bash

echo "Backing up /etc/apache2/conf/http.conf to /etc/apache2/conf/http.conf.bakvz"
echo "Changing Apache to Listen on 127.0.0.1:8080"

_TEXT_TO_FIND="Listen 0.0.0.0"

_BEST_PATH_LINE_NUMBER=$(grep -n "${_TEXT_TO_FIND}" /etc/apache2/conf/httpd.conf | head -1 | cut -d: -f1)
_LINE_TO_EDIT=$(($_BEST_PATH_LINE_NUMBER+2))
_TOTAL_LINES=$( wc -l /etc/apache2/conf/httpd.conf | cut -c -3)
if [[ $_LINE_TO_EDIT -lt $_TOTAL_LINES ]]; then
    sed -i.bakvz "${_LINE_TO_EDIT}i\Listen 127\.0\.0\.1\:8080\n" /etc/apache2/conf/httpd.conf
fi


echo "Backing up /etc/apache2/conf/http.conf to /etc/apache2/conf/http.conf.bakvz1"
echo "Uncommented userdata conf dir"

sed -i.bakvz1 -e 's/# Include "\/etc\/apache2\/conf.d\/userdata\/std\/2_4\//Include "\/etc\/apache2\/conf.d\/userdata\/std\/2_4\//g' /etc/apache2/conf/httpd.conf
sed -i.bakvz1 -e 's/# Include "\/etc\/apache2\/conf.d\/userdata\/ssl\/2_4\//Include "\/etc\/apache2\/conf.d\/userdata\/ssl\/2_4\//g' /etc/apache2/conf/httpd.conf


echo "touching varnish.conf in virtualhost userdata"
IFS=$'\n' GLOBIGNORE='*' command eval  '_DOMAINS=($(cat /etc/trueuserdomains | cut -d: -f1))'
IFS=$'\n' GLOBIGNORE='*' command eval  '_USERS=($(cat /etc/trueuserdomains | cut -d: -f2))'
_tLEN=${#_USERS[@]}

for (( i=0; i<${_tLEN}; i++ ));
do
    USER=$(echo ${_USERS[$i]} | sed -e 's/  *$//')
    DOMAIN=$(echo ${_DOMAINS[$i]} | sed -e 's/ *$//')
    mkdir -p /etc/apache2/conf.d/userdata/std/2_4/$USER/$DOMAIN/
    touch /etc/apache2/conf.d/userdata/std/2_4/$USER/$DOMAIN/varnish.conf
    mkdir -p /etc/apache2/conf.d/userdata/ssl/2_4/$USER/$DOMAIN/
    touch /etc/apache2/conf.d/userdata/ssl/2_4/$USER/$DOMAIN/varnish.conf
done

