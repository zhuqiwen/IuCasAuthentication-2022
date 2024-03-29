<?php

namespace Edu\IU\VPCM\IULoginCAS;

class IULoginCAS2
{

    public const CAS_SESSION_START = 'CAS_SESSION_START';
    // 15 minutes
    public const CAS_SESSION_TIME = 900;
    public const CASE_SESSION_USER_KEY = 'CAS_USER';

    private $casUrlProd;
    private $casUrlPreProd;
    private $username;
    private $firstLetter;

    public function __construct(string $mode = 'prod')
    {
        if (!isset($_SESSION)) {
            session_start();
        }

        $method = 'init' . $mode;
        if (method_exists($this, $method)) {
            $this->$method();
        } else {
            $msg = 'Illegal parameter value: ';
            $msg .= $mode;
            $msg .= 'new $IULoginCAS32($mode), $mode should be either \'prod\' or \'test\'; ';
            throw new \RuntimeException($msg);
        }
    }

    private function initProd()
    {
        $this->casUrlProd = 'https://idp.login.iu.edu/idp/profile/cas';
        $this->casUrlPreProd = 'https://idp-stg.login.iu.edu/idp/profile/cas';
    }

    private function initTest()
    {
        $this->casUrlProd = 'http://localhost:12345';
        $this->casUrlPreProd = 'http://localhost:12345';
    }


    public function authenticate()
    {
        //add checker to see if it's localhost server for dev.
        //if so, skip authenticate and set a fake username for development
        if($this->isLocalhost()){
            $this->setUserName('fake_user');
            return;
        }

        if ($this->isSessionExpired()) {
            $this->login();
        } elseif ($this->getCasTicket()) {
            $this->validate();
        } elseif ($this->isUserNotLogged()) {
            $this->login();
        }
    }

    protected function login()
    {
        $_SESSION[self::CAS_SESSION_START] = time();
        header('Location: ' . $this->getLoginUrl(), true, 303);
        exit();
    }

    protected function validate()
    {
        //start curl
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $this->getValidateUrl());
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $results = curl_exec($curl);

        //get username
        if (strpos($results, 'cas:authenticationSuccess') !== false) {
            $results = simplexml_load_string($results);
            $this->setUserName((string)$results->xpath('cas:authenticationSuccess/cas:user')[0]);
        }

    }


    //TODO: add logout

    //checkers
    public function isLocalhost()
    {
        return $_SERVER['SERVER_NAME'] == 'localhost';
    }


    public function isSessionExpired(): bool
    {
        $result = true;
        if (isset($_SESSION[self::CAS_SESSION_START])) {
            $result = (time() - $_SESSION[self::CAS_SESSION_START] > self::CAS_SESSION_TIME);
        }

        return $result;
    }

    public function isUserNotLogged(): bool
    {
        return !isset($_SESSION[self::CASE_SESSION_USER_KEY]);
    }


    public function getUserName()
    {
        return $this->username ?? $_SESSION[self::CASE_SESSION_USER_KEY] ?? null;
    }

    public function setUserName(?string $name)
    {
        $this->username = $name;

        $this->firstLetter = substr($name, 0, 1);
        if($name === null){
            unset($_SESSION[self::CASE_SESSION_USER_KEY]);
        }else{
            $_SESSION[self::CASE_SESSION_USER_KEY] = $name;
        }
    }

    public function getCasTicket()
    {
        return $_GET['ticket'] ?? null;
    }

    public function getServiceUrl(): string
    {

        $isHttps = $_SERVER['HTTPS'] == 'on';
        $urlHead = $isHttps ? 'https://' : 'http://';

        $port = $isHttps ?
            ($_SERVER['SERVER_PORT'] != '443' ? $_SERVER['SERVER_PORT'] : '')
            :
            ($_SERVER['SERVER_PORT'] != '80' ? $_SERVER['SERVER_PORT'] : '');

        $port = empty($port) ? '' : ':' . $port;

        //prepare url for /serviceValidate
        $requestUri = $_SERVER['REQUEST_URI'];
        if (isset($_GET['ticket'])) {
            $requestUri = str_replace('?ticket=' . $_GET['ticket'], '', $_SERVER['REQUEST_URI']);
        }
        $url = $urlHead . $_SERVER['HTTP_HOST'] . $port . $requestUri;

        return rawurlencode($url);
    }

    public function getCasUrlBase(): string
    {
        return substr_count($_SERVER['HTTP_HOST'], 'sitehost-test') ?
            $this->casUrlPreProd
            :
            $this->casUrlProd;
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


}
