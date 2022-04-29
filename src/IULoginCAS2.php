<?php

namespace Edu\IU\VPCM\IULoginCAS;

class IULoginCAS2{

    public const CAS_URL_PROD = 'https://idp.login.iu.edu/idp/profile/cas';
    public const CAS_URL_PRE_PROD = 'https://idp-stg.login.iu.edu/idp/profile/cas';
    public const CASE_SESSION_USER_KEY = 'CAS_USER';
    private $authenticated;
    private $username;




    public function getCurrentUrl(): string
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

    public function getCasUrl(): string
    {
        return substr_count(
            $this->getCurrentUrl(),
            'sitehost-test'
        )
            ?
            self::CAS_URL_PRE_PROD
            :
            self::CAS_URL_PROD;
    }


    public function isAuthenticated()
    {
        return $this->authenticated;
    }


    public function login()
    {
        if(!$this->authenticated){

        }
    }

    public function validate()
    {

    }

    public function getUserName()
    {
        return $this->username ?? $_SESSION[self::CASE_SESSION_USER_KEY] ?? null;
    }

    public function setUserName(?string $name)
    {

    }


}