#!/bin/bash

_TEXT_TO_FIND="Listen 0.0.0.0"

_BEST_PATH_LINE_NUMBER=$(grep -n "${_TEXT_TO_FIND}" /etc/apache2/conf/httpd.conf | head -1 | cut -d: -f1)
_LINE_TO_EDIT=$(($_BEST_PATH_LINE_NUMBER+2))
_TOTAL_LINES=$( wc -l /etc/apache2/conf/httpd.conf | cut -c -3)
if [[ $_LINE_TO_EDIT -lt $_TOTAL_LINES ]]; then
    sed -i.bakvz "${_LINE_TO_EDIT}i\Listen 127\.0\.0\.1\:8080\n" /etc/apache2/conf/httpd.conf
fi
