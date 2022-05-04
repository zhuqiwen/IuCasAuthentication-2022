<?php

namespace Edu\IU\VPCM\IULoginCAS;
require __DIR__ . '/../src/IULoginCAS2.php';

use phpDocumentor\Reflection\Types\This;
use PHPUnit\Framework\TestCase;

class IULoginCAS2Test extends TestCase{
    public const SERVER_DIR = __DIR__ . DIRECTORY_SEPARATOR . 'server';
    public const PID_FILE = self::SERVER_DIR . DIRECTORY_SEPARATOR . '.pids';

    private $cas;

    /**
     * set up local server for tests
     * @runInSeparateProcess
     */
    public static function setUpBeforeClass(): void
    {
        chdir(self::SERVER_DIR);
        shell_exec("php -S localhost:12345 > /dev/null 2>&1 & echo $! >> " . self::PID_FILE );
        sleep(1);
    }

    /**
     * destroy local server after test
     */
    public static function tearDownAfterClass(): void
    {
        $pids = file_exists(self::PID_FILE) ? file(self::PID_FILE) : false;
        self::localServer($pids);
    }


    /**
     * @param $pids
     * kill server if still active, otherwise just ls
     */
    public static function localServer($pids)
    {
        if(!$pids){
            return;
        }

        foreach ($pids as $pid){
            $cmd = posix_getpgid($pid) ? 'kill -9 ' . $pid : 'ls';
            shell_exec($cmd);
        }

        unlink(self::PID_FILE);
    }


    /**
     * setup client server info and instantiate IULoginCAS2
     */
    public function setUp(): void
    {
        $_SERVER['HTTP_HOST'] = 'localhost';
        $_SERVER['REQUEST_URI'] = '/test';


        $this->cas = new IULoginCAS2('test');

    }

    private function setupService(string $mode)
    {
        switch ($mode){
            CASE 'https and 443':
                $_SERVER['HTTPS'] = 'on';
                $_SERVER['SERVER_PORT'] = '443';
                break;
            CASE 'https and not 443':
                $_SERVER['HTTPS'] = 'on';
                $_SERVER['SERVER_PORT'] = '54321';
                break;
            CASE 'http and 80':
                $_SERVER['HTTPS'] = 'off';
                $_SERVER['SERVER_PORT'] = '80';
                break;
            CASE 'http and not 80':
                $_SERVER['HTTPS'] = 'off';
                $_SERVER['SERVER_PORT'] = '54321';
                break;
            default:
                break;
        }
    }


    /**
     * @runInSeparateProcess
     */
    public function testGetServiceUrl()
    {
        $_GET['ticket'] = 'abc';
        $this->setupService('http and not 80');
        self::assertSame('http://localhost:54321/test', $this->cas->getServiceUrl());
        $this->setupService('http and 80');
        self::assertSame('http://localhost/test', $this->cas->getServiceUrl());
        $this->setupService('https and not 443');
        self::assertSame('https://localhost:54321/test', $this->cas->getServiceUrl());
        $this->setupService('https and 443');
        self::assertSame('https://localhost/test', $this->cas->getServiceUrl());
        unset($_GET['ticket']);
    }

    /**
     * @runInSeparateProcess
     */
    public function testGetCasUrlBase()
    {
        self::assertSame('http://localhost:12345', $this->cas->getCasUrlBase());
    }

    /**
     * @runInSeparateProcess
     */
    public function testGetLoginUrl()
    {
        $this->setupService('http and not 80');
        self::assertSame('http://localhost:12345/login?service=http://localhost:54321/test', $this->cas->getLoginUrl());

        $this->setupService('http and 80');
        self::assertSame('http://localhost:12345/login?service=http://localhost/test', $this->cas->getLoginUrl());

        $this->setupService('https and not 443');
        self::assertSame('http://localhost:12345/login?service=https://localhost:54321/test', $this->cas->getLoginUrl());

        $this->setupService('https and 443');
        self::assertSame('http://localhost:12345/login?service=https://localhost/test', $this->cas->getLoginUrl());

    }

    /**
     * @runInSeparateProcess
     */
    public function testGetValidateUrl()
    {
        unset($_GET['ticket']);
        $this->setupService('http and not 80');
        self::assertSame('http://localhost:12345/serviceValidate?ticket=&service=http://localhost:54321/test', $this->cas->getValidateUrl());
        $this->setupService('http and 80');
        self::assertSame('http://localhost:12345/serviceValidate?ticket=&service=http://localhost/test', $this->cas->getValidateUrl());
        $this->setupService('https and not 443');
        self::assertSame('http://localhost:12345/serviceValidate?ticket=&service=https://localhost:54321/test', $this->cas->getValidateUrl());
        $this->setupService('https and 443');
        self::assertSame('http://localhost:12345/serviceValidate?ticket=&service=https://localhost/test', $this->cas->getValidateUrl());

        $_GET['ticket'] = 'dummy-ticket';
        $this->setupService('http and not 80');
        self::assertSame('http://localhost:12345/serviceValidate?ticket=dummy-ticket&service=http://localhost:54321/test', $this->cas->getValidateUrl());
        $this->setupService('http and 80');
        self::assertSame('http://localhost:12345/serviceValidate?ticket=dummy-ticket&service=http://localhost/test', $this->cas->getValidateUrl());
        $this->setupService('https and not 443');
        self::assertSame('http://localhost:12345/serviceValidate?ticket=dummy-ticket&service=https://localhost:54321/test', $this->cas->getValidateUrl());
        $this->setupService('https and 443');
        self::assertSame('http://localhost:12345/serviceValidate?ticket=dummy-ticket&service=https://localhost/test', $this->cas->getValidateUrl());
    }

}

