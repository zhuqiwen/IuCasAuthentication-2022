<?php

namespace Edu\IU\VPCM\IULoginCAS;

class IULoginCAS2{

    public const CAS_URL_PROD = 'https://idp.login.iu.edu/idp/profile/cas';
    public const CAS_URL_PRE_PROD = 'https://idp-stg.login.iu.edu/idp/profile/cas';
    public const CASE_SESSION_USER_KEY = 'CAS_USER';
    private $authenticated;
    private $username;




    public function getServiceUrl(): string
    {

        $isHttps = $_SERVER['HTTPS'] == 'on';
        $urlHead = $isHttps ? 'https://' : 'http://';

        $port = $isHttps ?
            ($_SERVER['SERVER_PORT'] != '443' ?? '')
            :
            ($_SERVER['SERVER_PORT'] != '80' ?? '');

        $port = empty($port) ? '' : ':' . $port;

        //prepare url for /serviceValidate
        $requestUri = str_replace('ticket=' . $_GET['ticket'], '', $_SERVER['REQUEST_URI']);

        return $urlHead . $_SERVER['HTTP_HOST'] . $port . $requestUri;
    }


    public function login()
    {
        $_SESSION['LAST_SESSION'] = time();
        header('Location: ' . $this->getLoginUrl(), true, 303);
        exit();
    }

    public function validate(): string
    {

        //start curl
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $this->getValidateUrl());
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $results = curl_exec($curl);

        //get username
        $results = simplexml_load_string($results);
        $username = (string)$results->xpath('cas:authenticationSuccess/cas:user')[0];

        //when validate fails, re-login
        if(empty($username)){
            $this->login();
        }

        return $username;

    }



    public function authenticate()
    {
        $action = $this->isAuthenticated() ? 'login' : 'validate';
        $this->$action();
    }

    public function logout()
    {

    }

    public function getUserName()
    {
        return $this->username ?? $_SESSION[self::CASE_SESSION_USER_KEY] ?? null;
    }

    public function setUserName(?string $name)
    {

    }

    public function getCasTicket()
    {
        return $_GET['ticket'] ?? null;
    }

    public function getCasUrlBase(): string
    {
        return substr_count($_SERVER['HTTP_HOST'], 'sitehost-test') ?
            self::CAS_URL_PRE_PROD
            :
            self::CAS_URL_PROD;
    }

    public function getLoginUrl(): string
    {
         return $this->getCasUrlBase() . DIRECTORY_SEPARATOR . 'login?service=' . $this->getServiceUrl();
    }

    public function getValidateUrl(): string
    {
        $ticket = $this->getCasTicket();
        $serviceUrl = $this->getServiceUrl();

        return $this->getCasUrlBase() . DIRECTORY_SEPARATOR . 'serviceValidate?ticket=' . $ticket . '&service=' . $serviceUrl;
    }


    public function isAuthenticated(): bool
    {
        $result = false;
        if(isset($_SESSION['LAST_SESSION'])){
            $result = !(time() - $_SESSION['LAST_SESSION'] > 900);
        }

        return $result;
    }
}