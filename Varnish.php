<?php

class Varnish 
{
    protected $cpanel;
    protected $userPath;
    protected $adminPort = null;
    protected $file = null;

    public function __construct(CPANEL $cpanel)
    {
        $this->cpanel = $cpanel;
        $processUser = posix_getpwuid(posix_geteuid());
        $user = $processUser['name'];
        $this->userPath = "/home/$user";
    }

    public function getUserPath() {
        return $this->userPath;
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
}

