<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\API\JavaScript;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Constraint\StringStartsWith;
use Slothsoft\FarahTesting\FarahServer;
use Slothsoft\FarahTesting\Exception\BrowserDriverNotFoundException;
use Slothsoft\Farah\FarahUrl\FarahUrlAuthority;
use Symfony\Component\Panther\Client;
use PHPUnit\Framework\Constraint\IsEqual;

class DOMTest extends TestCase {
    
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
    
    public function test_loadDocument(): void {
        $this->client->request('GET', '/slothsoft@test-module/tests/dom');
        
        $actual = $this->client->executeScript(<<<EOT
return DOM.loadDocument("/slothsoft@farah/phpinfo").querySelector("h1").textContent;
EOT);
        
        $this->assertThat($actual, new StringStartsWith('PHP Version'));
    }
    
    public function test_loadXML(): void {
        $this->client->request('GET', '/slothsoft@test-module/tests/dom');
        
        $actual = $this->client->executeScript(<<<EOT
return DOM.loadXML("<xml><h1>Success</h1></xml>").querySelector("h1").textContent;
EOT);
        
        $this->assertThat($actual, new IsEqual('Success'));
    }
    
    public function test_saveXML(): void {
        $this->client->request('GET', '/slothsoft@test-module/tests/dom');
        
        $actual = $this->client->executeScript(<<<EOT
const doc = document.implementation.createDocument(null, "xml");
doc.documentElement.textContent = "Success";
return DOM.saveXML(doc);
EOT);
        
        $this->assertThat($actual, new IsEqual('<xml>Success</xml>'));
    }
}