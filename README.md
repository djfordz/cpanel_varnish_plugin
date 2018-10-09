# Cpanel Varnish Plugin

## Installation

Varnish and Supervisord need to be installed first.

### Install nDeploy plugin

Install [Autom8n](https://www.autom8n.com/) but it is not free.

### For Varnish

Follow the docs here:
[Install Varnish 4.1.9](https://packagecloud.io/varnishcache/varnish41/install#manual-rpm)

### For Supervisord on CENTOS6

Follow the docs here:
[Install Supervisord 3.3.3](http://supervisord.org/installing.html)

### For Supervisord on CENTOS7

`yum install supervisord`

### Install Cpanel Varnish Plugin

run the install.sh script as root. (NOTE: Cpanel plugins can only be installed by root user)
`./install.sh`

## Usage

In the Advanced group of Cpanel User Menu, An Icon called Varnish Cache is now available.

Just enable for whichever directory you want to use varnish with.

NOTE: the varnish.vcl file MUST be in a subdirectory immediately under doc root `<doc root>/var/varnish.vcl` or the default `varnish.vcl` from `/etc/default.vcl` will be used negating any effects Varnish will have on your application.


