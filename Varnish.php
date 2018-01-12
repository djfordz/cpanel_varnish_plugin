<?php

class Varnish 
{
    const NEMKE_CONFIG = "/etc/nemke/supervisor.d/varnish.conf";
    const APACHE_GLOBAL_CONFIG = "/etc/apache2/conf.d/includes/post_virtualhost_global.conf";
    const APACHE_USER_CONFIG = "/etc/apache2/conf.d/userdata/std/2_4/";

    protected $cpanel;
    protected $userPath;
    protected $cpanelUser;
    protected $serverName;
    protected $vhostFile;
    protected $adminPort;
    protected $listenPort;
    protected $path;
    protected $config;

    public function __construct(CPANEL $cpanel)
    {
        $this->cpanel = $cpanel;
        $processUser = posix_getpwuid(posix_geteuid());
        $this->cpanelUser = $processUser['name'];
        $this->userPath = "/home/$this->cpanelUser";
        $this->serverName = $this->getMainDomain();
        $this->vhostFile = $this->cpanelUser . "/$this->serverName" . "/$this->cpanelUser" . "_varnish.conf";
        $this->config = $this->readConfig(self::NEMKE_CONFIG);
    }

    public function __($s) {
        return htmlspecialchars($s, ENT_QUOTES, 'utf-8');
    }

    public function getUserPath() {
        return $this->userPath;
    }

    public function getUser() {
        return $this->cpanelUser;
    }

    public function getPath() {
        return $this->path;
    }

    public function listDirs($dir = false)
    {
        $dir = urldecode($dir);

        if($dir == false) {
            $dir = $this->userPath;
        }

            $dirs = $this->cpanel->uapi(
                'Fileman', 'list_files',
                array(
                    'dir'                           => $dir,
                    'types'                         => 'dir',
                    'limit_to_list'                 => '0',
                    'show_hidden'                   => '1',
                    'check_for_leaf_directories'    => '1',
                    'include_mime'                  => '0',
                    'include_hash'                  => '0',
                    'include_permissions'           => '1'
                )
            );

        $list = array();
        
        foreach($dirs['cpanelresult']['result']['data'] as $d) {
            if(isset($d) && is_array($d)) {
                $list[] = array(
                    'dir'   => $d['absdir'], 
                    'type'  => $d['type'], 
                    'path'  => $d['fullpath'], 
                    'perms' => $d['nicemode']
                );
            }
        }
        
        return $list;
    }

    public function enable($dataPath) {
        $this->path = $dataPath;
        $this->adminPort = $this->getPort();
        $this->listenPort = $this->adminPort + 2000;
        $this->config[] = array("[program:$this->cpanelUser" . "_$this->adminPort" . "_$this->path]", $this->adminPort, $this->path);
        $this->applyNemkeConfig();
        $this->applyApacheConfig();
        $this->reload('httpd');
        $this->reload('supervisord');
        
    }

    public function disable($dataPath, $port) {
        $this->path = $dataPath;
        $this->adminPort = $port;

        $program = $this->cpanelUser . "_" . $this->adminPort . "_" . $this->path;
        
        $this->stopVarnish($program);
        $this->removeApacheConfig();
        $this->removeNemkeConfig();
        $this->reload('httpd');
        $this->reload('supervisord');
    }

    public function display() {
        $userConfigs = array();
        if(count($this->config) > 0) {
            foreach($this->config as $config) {
                if(isset($config[0])) {
                    if(strstr($config[0], $this->cpanelUser)) {
                        $userConfigs[] = $config;
                    }
                }
            }
        }
        return $userConfigs;
        
        
    }

    protected function getPort() {
        $config = array();
        foreach($this->config as $value) {
            $config[] = $value[1];
        }

        $port = rand(6000, 6500);
        
        if(isset($config)) {
            if(in_array($port, $config)) {
                $this->getPort(); 
            }
        } 

        return $port;
    }

    protected function removeApacheConfig() {
        $len = strlen($this->cpanelUser) + strlen($this->path) + 8;
        $globalConf = $this->_read(self::APACHE_GLOBAL_CONFIG);
        $userConf = $this->_read(self::APACHE_USER_CONFIG . $this->vhostFile);

        preg_match("/# start $this->cpanelUser $this->path/", $globalConf, $globalMatchStart);
        preg_match("/# end $this->cpanelUser $this->path/", $globalConf, $globalMatchEnd);
        preg_match("/# start $this->cpanelUser $this->path/", $userConf, $userMatchStart);
        preg_match("/# end $this->cpanelUser $this->path/", $userConf, $userMatchEnd);

        if(isset($globalMatchStart[0]) && isset($globalMatchEnd[0])) {
            $globalPosStart = strpos($globalConf, $globalMatchStart[0]);
            $globalPosEnd = strpos($globalConf, $globalMatchEnd[0]);
            $newGlobalConf = substr_replace($globalConf, '', $globalPosStart, ($globalPosEnd - $globalPosStart) + $len);
            $this->overwrite(self::APACHE_GLOBAL_CONFIG, $newGlobalConf);
            
        }

        if(isset($userMatchStart[0]) && isset($userMatchEnd[0])) {
            $userPosStart = strpos($userConf, $userMatchStart[0]);
            $userPosEnd = strpos($userConf, $userMatchEnd[0]);
            $newUserConf = substr_replace($userConf, '', $userPosStart, ($userPosEnd - $userPosStart) + $len);
            $this->overwrite(self::APACHE_USER_CONFIG . $this->vhostFile, $newUserConf);
        }
    }

    protected function removeNemkeConfig() {
        $len = strlen($this->cpanelUser) + strlen($this->path) + 8;
        $conf = $this->_read(self::NEMKE_CONFIG);
        $m1 = preg_match("/# start $this->cpanelUser $this->path/", $conf, $matchStart);
        $m2 = preg_match("/# end $this->cpanelUser $this->path/", $conf, $matchEnd);

        if($m1 == 1 && $m2 == 1) {
            $posStart = strpos($conf, $matchStart[0]);
            $posEnd = strpos($conf, $matchEnd[0]);
            $newConf = substr_replace($conf, '', $posStart, ($posEnd - $posStart) + $len);
            $this->overwrite(self::NEMKE_CONFIG, $newConf);
        }
        
    }
    protected function getMainDomain() {
        $domains = $this->cpanel->uapi(
            'DomainInfo', 'list_domains'
        );

        return $domains['cpanelresult']['result']['data']['main_domain'];
    }

    protected function _read($path) {
        if(!is_file($path)) {
            return;
        }

        $result = $this->cpanel->uapi(
            'NemjVarnish',
            'read',
            array(
                'path' => $path
            )
        );
        return $result['cpanelresult']['result']['data'];
    }

    protected function write($path, $data) {
        $result = $this->cpanel->uapi(
            'NemjVarnish', 'write',
            array(
                'path' => $path,
                'data' => $data,
            )
        );

        return $result;
    }

    protected function overwrite($path, $data) {
        $result = $this->cpanel->uapi(
            'NemjVarnish', 'overwrite',
            array(
                'path' => $path,
                'data' => $data,
            )
        );

        return $result;
    }

    protected function start($program) {
        $result = $this->cpanel->uapi(
            'NemjVarnish', 'start',
            array(
                'program' => $program
            )
        );

        return $result;
    }

    protected function stop($program) {
        $result = $this->cpanel->uapi(
            'NemjVarnish', 'stop',
            array(
                'program' => $program
            )
        );

        return $result;
    }

    protected function restart($program) {
        $result = $this->cpanel->uapi(
            'NemjVarnish', 'restart',
            array(
                'program' => $program
            )
        );

        return $result;
    }

    protected function reload($program) {
        $result = $this->cpanel->uapi(
            'NemjVarnish', 'reload',
            array(
                'program' => $program
            )
        );

        return $result;
    }

    protected function startVarnish($program) {
        $result = $this->cpanel->uapi(
            'NemjVarnish', 'start_varnish',
            array(
                'program' => $program
            )
        );

        return $result;
    }

    protected function stopVarnish($program = null) {
        if ($program == null) {
            $program = $this->cpanelUser . '_' . $this->adminPort . '_' . $this->path;
        }

        $result = $this->cpanel->uapi(
            'NemjVarnish', 'stop_varnish',
            array(
                'program' => $program
            )
        );

        return $result;
    }

    public function restartVarnish($program = null) {
        if ($program == null) {
            $program = $this->cpanelUser . '_' . $this->adminPort . '_' . $this->path;
        }
        
        $result = $this->cpanel->uapi(
            'NemjVarnish', 'restart_varnish',
            array(
                'program' => $program
            )
        );

        return $result;
    }

    protected function reloadVarnish($program = null) {
        if ($program == null) {
            $program = $this->cpanelUser . '_' . $this->adminPort . '_' . $this->path;
        }

        $result = $this->cpanel->uapi(
            'NemjVarnish', 'reload_varnish',
            array(
                'program' => $program
            )
        );

        return $result;
    }

    protected function readConfig($path) {
        
        $conf = $this->_read($path);
        if ($path == self::NEMKE_CONFIG) {
            $m1 = preg_match_all("/\[program:$this->cpanelUser" . '_(\d*)_(.*)\]/', $conf, $matches);
            $config = array();

            if($m1 == 1) {
                $flat = $this->flatten($matches);
                $config[] = $flat;
            } else if ($m1 > 1) {
                for($i = 0; $i < $m1; $i++) {
                        $temp = array();
                    for($j = 0; $j < 3; $j++) {
                        $temp[] = $matches[$j][$i];
                    }
                        $config[] = $temp;
                }
            } else {
                $config = array();
            }
        } else {
            $config[] = $conf;
        }

        return $config;
    }

    private function flatten($array) {
        if (!is_array($array)) {
            return array($array);
        }

        $result = array();
        foreach($array as $value) {
            $result = array_merge($result, $this->flatten($value));
        }

        return $result;
    }

    protected function applyNemkeConfig() {
        $vcl = file_exists($this->userPath . "/" . $this->path . '/var/varnish.vcl') ? $this->userPath . "/$this->path" . '/var/varnish.vcl' : '/etc/varnish/default.vcl';

        $data = $this->_read(self::NEMKE_CONFIG);

        $data .= "# start $this->cpanelUser $this->path
[program:$this->cpanelUser" . "_$this->adminPort" . "_$this->path]
command = /usr/sbin/varnishd -j unix,user=$this->cpanelUser -F -a 127.0.0.1:$this->listenPort -T 127.0.0.1:$this->adminPort -f $vcl -t 120 -p thread_pool_min=50 -p thread_pool_max=1000 -p thread_pool_timeout=120 -S /etc/varnish/secret -s malloc,512M -p feature=+esi_ignore_other_elements -p vcc_allow_inline_c=on -p cli_buffer=32k -p pipe_timeout=7200 -p workspace_client=512k -p http_resp_hdr_len=32k
autostart = true
user = root 
startsecs = 5
stdout_logfile = /var/log/varnish/$this->cpanelUser.log
stdout_logfile_maxbytes = 10MB
stdout_logfile_backups = 4
stderr_logfile = /var/log/varnish/$this->cpanelUser.log
stderr_logfile_maxbytes = 10MB
stderr_logfile_backups = 4
# end $this->cpanelUser $this->path
\n";

        $this->overwrite(self::NEMKE_CONFIG, $data);
    }

    protected function applyApacheConfig() {
        
        $userConfig = $this->_read(self::APACHE_USER_CONFIG . $this->vhostFile);

        $userConfig .= "# start $this->cpanelUser $this->path
SetEnvIfNoCase ^X-Forwarded-Proto$ \"https\" HTTPS=on

ProxyPreserveHost On
ProxyPass / http://127.0.0.1:$this->listenPort/
ProxyPassReverse / http://127.0.0.1:$this->listenPort/
# end $this->cpanelUser $this->path
\n";

        $this->overwrite(self::APACHE_USER_CONFIG . $this->vhostFile, $userConfig);

        $globalConfig = $this->_read(self::APACHE_GLOBAL_CONFIG);

        $globalConfig .= "# start $this->cpanelUser $this->path
<VirtualHost 127.0.0.1:8080>
    ServerName $this->serverName 
    ServerAlias mail.$this->serverName www.$this->serverName
    DocumentRoot $this->userPath" . "/$this->path 
    ServerAdmin webmaster@$this->serverName
    CustomLog /dev/null combined

    ## User $this->cpanelUser # Needed for Cpanel::ApacheConf
    <IfModule userdir_module>
    <IfModule !mpm_itk.c>
      <IfModule !ruid2_module>
        <IfModule !mod_passenger.c>
          UserDir disabled
          UserDir enabled $this->cpanelUser
        </IfModule>
      </IfModule>
    </IfModule>
    </IfModule>

    # Enable backwards compatible Server Side Include expression parser for Apache versions >= 2.4.
    # To selectively use the newer Apache 2.4 expression parser, disable SSILegacyExprParser in
    # the user's .htaccess file.  For more information, please read:
    #    http://httpd.apache.org/docs/2.4/mod/mod_include.html#ssilegacyexprparser
    <IfModule include_module>
    <Directory $this->userPath" . "/$this->path>
      SSILegacyExprParser On
    </Directory>
    </IfModule>
        
    <Directory $this->userPath>
    AllowOverride All
    </Directory>
        
    <IfModule suphp_module>
    suPHP_UserGroup $this->cpanelUser $this->cpanelUser 
    </IfModule>
    <IfModule suexec_module>
    <IfModule !mod_ruid2.c>
      SuexecUserGroup $this->cpanelUser $this->cpanelUser
    </IfModule>
    </IfModule>
    <IfModule ruid2_module>
    RMode config
    RUidGid $this->cpanelUser $this->cpanelUser
    </IfModule>
    <IfModule mpm_itk.c>

    # For more information on MPM ITK, please read:
    #   http://mpm-itk.sesse.net/
    AssignUserID $this->cpanelUser $this->cpanelUser
    </IfModule>
    <IfModule mod_passenger.c>
    PassengerUser $this->cpanelUser 
    PassengerGroup $this->cpanelUser 
    </IfModule>

    <IfModule alias_module>
        ScriptAlias /cgi-bin/ $this->userPath" . "/$this->path" . "/cgi-bin/
    </IfModule>

    # Global DCV Rewrite Exclude
    <IfModule rewrite_module>
        RewriteOptions Inherit
    </IfModule>

</VirtualHost>
# end $this->cpanelUser $this->path
\n";

        
        $this->overwrite(self::APACHE_GLOBAL_CONFIG, $globalConfig);
    }

}
