<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\API\JavaScript;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Constraint\IsEqual;
use Slothsoft\FarahTesting\FarahServer;
use Slothsoft\FarahTesting\Exception\BrowserDriverNotFoundException;
use Slothsoft\Farah\FarahUrl\FarahUrlAuthority;
use Symfony\Component\Panther\Client;

class DOMTest extends TestCase {
    
    private static ?FarahServer $server;
    
    private ?Client $client = null;
    
    public static function setUpBeforeClass(): void {
        self::$server = new FarahServer();
        self::$server->setModule(FarahUrlAuthority::createFromVendorAndModule('slothsoft', 'test-module'), realpath('test-files/test-module'));
        self::$server->start();
    }
    
    public static function tearDownAfterClass(): void {
        self::$server = null;
    }
    
    protected function setUp(): void {
        try {
            $this->client = self::$server->createClient();
        } catch (BrowserDriverNotFoundException $e) {
            $this->markTestSkipped();
        }
    }
    
    protected function tearDown(): void {
        if ($this->client) {
            $this->client->quit();
            unset($this->client);
        }
    }
    
    /**
     *
     * @dataProvider provideDocuments
     */
    public function test_loadDocument(string $uri, string $expected): void {
        $this->client->request('GET', '/slothsoft@test-module/tests/dom');
        
        $arguments = [
            $uri
        ];
        
        $actual = $this->client->executeScript(<<<EOT
return Test.run((uri) => {
    const doc = DOM.loadDocument(uri);
    return doc.querySelector("h1").textContent;
}, arguments);
EOT, $arguments);
        
        $this->assertThat($actual, new IsEqual($expected));
    }
    
    /**
     *
     * @dataProvider provideDocuments
     */
    public function test_loadDocumentAsync(string $uri, string $expected): void {
        $this->client->request('GET', '/slothsoft@test-module/tests/dom');
        
        $arguments = [
            $uri
        ];
        
        $actual = $this->client->executeAsyncScript(<<<EOT
Test.runAsync(async (uri) => {
    const doc = await DOM.loadDocumentAsync(uri);
    return doc.querySelector("h1").textContent;
}, arguments);
EOT, $arguments);
        
        $this->assertThat($actual, new IsEqual($expected));
    }
    
    public function provideDocuments(): iterable {
        yield 'phpinfo' => [
            '/slothsoft@farah/phpinfo',
            'PHP Version ' . PHP_VERSION
        ];
        
        yield 'HTML' => [
            '/slothsoft@test-module/document',
            'HTML'
        ];
        
        yield 'farah' => [
            'farah://slothsoft@test-module/document',
            'HTML'
        ];
    }
    
    /**
     *
     * @dataProvider provideTextContent
     */
    public function test_loadXML(string $content): void {
        $this->client->request('GET', '/slothsoft@test-module/tests/dom');
        
        $arguments = [
            $content
        ];
        
        $actual = $this->client->executeScript(<<<EOT
return Test.run((content) => {
    const doc = DOM.loadXML("<xml><h1>" + content + "</h1></xml>");
    return doc.querySelector("h1").textContent;
}, arguments);
EOT, $arguments);
        
        $this->assertThat($actual, new IsEqual($content));
    }
    
    /**
     *
     * @dataProvider provideTextContent
     */
    public function test_saveXML(string $content): void {
        $this->client->request('GET', '/slothsoft@test-module/tests/dom');
        
        $arguments = [
            $content
        ];
        
        $actual = $this->client->executeScript(<<<EOT
return Test.run((content) => {
    const doc = document.implementation.createDocument(null, "xml");
    doc.documentElement.textContent = content;
    return DOM.saveXML(doc);
}, arguments);
EOT, $arguments);
        
        $this->assertThat($actual, new IsEqual("<xml>$content</xml>"));
    }
    
    public function provideTextContent(): iterable {
        yield [
            'hello world'
        ];
        yield [
            'success'
        ];
    }
}