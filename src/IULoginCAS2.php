<?php

namespace Edu\IU\VPCM\IULoginCAS;

class IULoginCAS2{

    public function getCurrentURL(): string
    {

        $isHttps = $_SERVER['HTTPS'] == 'on';
        $urlHead = $isHttps ? 'https://' : 'http://';

        $port = $isHttps ?
            ($_SERVER['SERVER_PORT'] != '443' ?? '')
            :
            ($_SERVER['SERVER_PORT'] != '80' ?? '');

        $port = empty($port) ? '' : ':' . $port;

        return $urlHead . $_SERVER['HTTP_HOST'] . $port . $_SERVER['REQUEST_URI'];
    }
    
}