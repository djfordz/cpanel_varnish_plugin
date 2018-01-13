# Cpanel Varnish Plugin

## Installation

Varnish and Supervisord need to be installed first.

### For Varnish

Follow the docs here:
[Install Varnish 4.1.9](https://packagecloud.io/varnishcache/varnish41/install#manual-rpm)

### For Supervisord

Follow the docs here:
[Install Supervisord 3.3.3](http://supervisord.org/installing.html)

Note:: Plugin has only been tested with Varnish version 4.1.9 and Supervisord version 3.3.3

If you use any other versions, please let me know if it works or not by submitting an issue.

### If you wish to use Varnish over https you must install an Nginx Reverse Proxy

We recommend using [Autom8n](https://www.autom8n.com/) but it is not free.
This is what this plugin is tested with, if you use any other nginx reverse proxy, let us know if it works.

### Install Cpanel Varnish Plugin

run the install.sh script as root. (NOTE: Cpanel plugins can only be installed by root user)
`./install.sh`

In http.conf, there is one thing you must do:
uncomment/add this line to end of every `<VirtualHost>`

## Usage

In the Advanced group of Cpanel User Menu, An Icon called Varnish Cache is now available.

Just enable for whichever directory you want to use varnish with.

NOTE: the varnish.vcl file MUST be in a subdirectory immediately under doc root `<doc root>/var/varnish.vcl` or the default `varnish.vcl` from `/etc/default.vcl` will be used negating any effects Varnish will have on your application.


