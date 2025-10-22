<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\API\JavaScript;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Constraint\IsEqual;
use Slothsoft\FarahTesting\FarahServer;
use Slothsoft\Farah\FarahUrl\FarahUrlAuthority;

class XSLTTest extends TestCase {
    
    private FarahServer $server;
    
    protected function setUp(): void {
        $this->server = new FarahServer();
        $this->server->setModule(FarahUrlAuthority::createFromVendorAndModule('slothsoft', 'test-module'), 'test-files/test-module');
        $this->server->start();
    }
    
    protected function tearDown(): void {
        unset($this->server);
    }
    
    public function test_transformToFragment(): void {
        $client = $this->server->createClient();
        $client->request('GET', '/slothsoft@test-module/test/xslt');
        
        $actual = $client->executeScript(<<<EOT
return "XSLT";
EOT);
        
        $this->assertThat($actual, new IsEqual('XSLT'));
    }
}