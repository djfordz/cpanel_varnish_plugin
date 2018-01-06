# Cpanel Varnish Plugin

This cpanel plugin allows users to control Varnish Cache within their panel. It will allow users to select a directory to start Varnish cache, automatically select an admin port and listening port, and read the varnish.vcl out of the var/ directory of any selected directory it is started on.

## Pre-Installation Requirements

Varnish Cache and Supervisord are necessary to be installed on the server before this plugin will work.

### Varnish Installation Instructions

CentOS6 - 

```
curl -s https://packagecloud.io/install/repositories/varnishcache/varnish41/script.rpm.sh | sudo bash
sudo yum install varnish-devel-4.1.9-1.el6.x86_64
```

per the directions here https://varnish-cache.org/docs/4.1/installation/install.html

### Installing Supervisord

Follow the directions at http://supervisord.org/installing.html


NOTE: This plugin is only tested with supervisord version 3.3.3 and Varnish version 4.1.9, so if you are able to get it working with other versions let me know!


Once supervisord and Varnish is installed.

Add the following to `/etc/supervisord.conf`

```
[rpcinterface:supervisor]
supervisor.rpcinterface_factory = supervisor.rpcinterface:make_main_rpcinterface

[unix_http_server]
file = /var/run/supervisor.sock
chmod = 0775
chown = root:root

[supervisord]
identifier = supervisord
logfile = /var/log/supervisor/supervisord.log
logfile_backups = 4
pidfile = /var/run/supervisord.pid

[supervisorctl]
serverurl=unix:///var/run/supervisor.sock

[include]
files = /etc/nemke/supervisor.d/*.conf
```

pre-setup is now complete.

###Installing Cpanel Varnish Plugin

download the zip or tar from the releases to the server you want to install on

unzip/tar and run the install.sh script. This must be run as root or with sudo privileges, only cPanel plugins can be installed by root.

`./install.sh`

That is it!

### Usage

Navigate to cPanel, under Advanced group you should see Varnish Cache Icon.

Select the directory you wish to use varnish on, ensure there is a file called `varnish.vcl` in a `var/` directory directly decended from the parent directory you select to run Varnish or the `/etc/vanrish/varnish.vcl` default vcl will be used.

Varnish should now run after selecting Update.

You can check with `ps aux | grep varnishd`

To disable simply select the disable button.

NOTE: There is currently a bug, where all Varnish instances under the one selected for disable, get disabled as well, we are working on this.


