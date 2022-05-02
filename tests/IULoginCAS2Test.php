<?php

namespace Edu\IU\VPCM\IULoginCAS;
require __DIR__ . '/../src/IULoginCAS2.php';

use phpDocumentor\Reflection\Types\This;
use PHPUnit\Framework\TestCase;

class IULoginCAS2Test extends TestCase{
    public const SERVER_DIR = __DIR__ . DIRECTORY_SEPARATOR . 'server';
    public const PID_FILE = self::SERVER_DIR . DIRECTORY_SEPARATOR . '.pids';

    /**
     * set up local server for tests
     */
    public static function setUpBeforeClass(): void
    {
        chdir(self::SERVER_DIR);
        shell_exec("php -S localhost:12345 > /dev/null 2>&1 & echo $! >> " . self::PID_FILE );
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
    }


    public function setUp(): void
    {

    }

    public function testGetCasTicket()
    {
        
    }

    public function testGetServiceUrl()
    {
        
    }

    public function testGetCasUrlBase()
    {
        
    }

    public function testGetLoginUrl()
    {
        
    }

    public function testGetValidateUrl()
    {
        
    }

    public function testIsAuthenticated()
    {
        
    }
}

