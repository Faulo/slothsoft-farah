<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\API\XSL;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Constraint\IsEqual;
use Slothsoft\FarahTesting\FarahServer;
use Slothsoft\FarahTesting\Exception\BrowserDriverNotFoundException;
use Slothsoft\Farah\FarahUrl\FarahUrlAuthority;
use Symfony\Component\Panther\Client;

class HtmlTest extends TestCase {
    
    private FarahServer $server;
    
    private ?Client $client = null;
    
    protected function setUp(): void {
        $this->server = new FarahServer();
        $this->server->setModule(FarahUrlAuthority::createFromVendorAndModule('slothsoft', 'test-module'), realpath('test-files/test-module'));
        $this->server->start();
        
        try {
            $this->client = $this->server->createClient();
        } catch (BrowserDriverNotFoundException $e) {
            $this->markTestSkipped();
        }
    }
    
    protected function tearDown(): void {
        if ($this->client) {
            $this->client->quit();
            unset($this->client);
        }
        
        unset($this->server);
    }
    
    public function test_titleIsUrl(): void {
        $this->client->request('GET', '/slothsoft@test-module/tests/html');
        
        $actual = $this->client->executeScript(<<<EOT
return document.querySelector("title").textContent;
EOT);
        
        $this->assertThat($actual, new IsEqual('farah://slothsoft@test-module/tests/html'));
    }
}