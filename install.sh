#!/bin/bash

set -e # Abort script at first error

cwd=$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )
install_plugin='/usr/local/cpanel/scripts/install_plugin'
dst='/usr/local/cpanel/base/frontend/paper_lantern/nemj_varnish'
api='/usr/local/cpanel/Cpanel/API'
adm='/usr/local/cpanel/bin/admin/Nemanja'

if [ $EUID -ne 0 ]; then
    echo 'Script requires root privileges, run it as root or with sudo'
    exit 1
fi

if [ ! -f /usr/local/cpanel/version ]; then
    echo 'cPanel installation not found'
    exit 1
fi

if [ -d $dst ]; then
    echo "Existing installation found, try running the uninstall script first"
    exit 1
else
    mkdir -v $dst
fi

if [ ! -d $adm ]; then
    mkdir -v $adm
fi

cp -v ${cwd}/index.live.php $dst
cp -v ${cwd}/Varnish.php $dst
cp -v ${cwd}/varnish.css $dst
cp -v ${cwd}/disable.live.php $dst
cp -v ${cwd}/enable.live.php $dst
cp -v ${cwd}/Cpanel/API/NemjVarnish.pm $api
cp -v ${cwd}/bin/admin/Nemanja/Varnish.conf $adm
cp -v ${cwd}/bin/admin/Nemanja/Varnish $adm

mkdir -p /etc/nemke/supervisor.d/
touch /etc/nemke/supervisor.d/varnish.conf

chmod 755 /etc/nemke/
chmod 755 /etc/nemke/supervisor.d/
chmod 644 /etc/nemke/supervisor.d/varnish.conf
chmod 700 ${adm}/Varnish

themes=('paper_lantern')

for theme in ${themes[@]}; do
    $install_plugin ${cwd}/plugins/${theme} --theme $theme
done

./fix_httpd.sh

echo 'Installation finished without errors'
