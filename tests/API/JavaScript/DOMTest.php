<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\API\JavaScript;

use PHPUnit\Framework\Constraint\IsEqual;
use Slothsoft\FarahTesting\FarahServerTestCase;
use Slothsoft\Farah\FarahUrl\FarahUrlAuthority;

final class DOMTest extends FarahServerTestCase {
    
    protected static function setUpServer(): void {
        self::$server->setModule(FarahUrlAuthority::createFromVendorAndModule('slothsoft', 'test-module'), 'test-files/test-module');
    }
    
    protected function setUpClient(): void {
        $this->client->request('GET', '/');
    }
    
    /**
     *
     * @dataProvider provideDocuments
     */
    public function test_loadDocument(string $uri, string $expected): void {
        $arguments = [
            $uri
        ];
        
        $actual = $this->client->executeAsyncScript(<<<EOT
async function test(uri) {
    const { default: sut } = await import("/slothsoft@farah/js/DOM");

    const doc = sut.loadDocument(uri);

    return doc.querySelector("h1").textContent;
}
            
import("/slothsoft@farah/js/Test").then(Test => Test.run(test, arguments));
EOT, $arguments);
        
        $this->assertThat($actual, new IsEqual($expected));
    }
    
    /**
     *
     * @dataProvider provideDocuments
     */
    public function test_loadDocumentAsync(string $uri, string $expected): void {
        $arguments = [
            $uri
        ];
        
        $actual = $this->client->executeAsyncScript(<<<EOT
async function test(uri) {
    const { default: sut } = await import("/slothsoft@farah/js/DOM");

    const doc = await sut.loadDocumentAsync(uri);

    return doc.querySelector("h1").textContent;
}

import("/slothsoft@farah/js/Test").then(Test => Test.run(test, arguments));
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
        $arguments = [
            $content
        ];
        
        $actual = $this->client->executeAsyncScript(<<<EOT
async function test(content) {
    const { default: sut } = await import("/slothsoft@farah/js/DOM");

    const doc = sut.loadXML("<xml><h1>" + content + "</h1></xml>");

    return doc.querySelector("h1").textContent;
}
            
import("/slothsoft@farah/js/Test").then(Test => Test.run(test, arguments));
EOT, $arguments);
        
        $this->assertThat($actual, new IsEqual($content));
    }
    
    /**
     *
     * @dataProvider provideTextContent
     */
    public function test_saveXML(string $content): void {
        $arguments = [
            $content
        ];
        
        $actual = $this->client->executeAsyncScript(<<<EOT
async function test(content) {
    const { default: sut } = await import("/slothsoft@farah/js/DOM");

    const doc = document.implementation.createDocument(null, "xml");
    doc.documentElement.textContent = content;

    return sut.saveXML(doc);
}
            
import("/slothsoft@farah/js/Test").then(Test => Test.run(test, arguments));
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