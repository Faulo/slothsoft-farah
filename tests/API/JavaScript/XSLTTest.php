<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\API\JavaScript;

use PHPUnit\Framework\Constraint\IsEqual;
use PHPUnit\Framework\Constraint\StringStartsWith;
use Slothsoft\Core\DOMHelper;
use Slothsoft\FarahTesting\FarahServerTestCase;
use Slothsoft\Farah\FarahUrl\FarahUrlAuthority;
use Slothsoft\Farah\Module\Module;
use Slothsoft\FarahTesting\Constraints\DOMNodeEqualTo;

final class XSLTTest extends FarahServerTestCase {
    
    private static FarahUrlAuthority $authority;
    
    protected static function setUpServer(): void {
        self::$authority = FarahUrlAuthority::createFromVendorAndModule('slothsoft', 'test-module');
        
        self::$server->setModule(self::$authority, 'test-files/test-module');
    }
    
    protected function setUpClient(): void {
        $this->client->request('GET', '/slothsoft@farah/example-page');
        
        Module::registerWithXmlManifestAndDefaultAssets(self::$authority, 'test-files/test-module');
    }
    
    public function test_transformToFragment_exists(): void {
        $arguments = [];
        
        $actual = $this->client->executeAsyncScript(<<<EOT
async function test() {
    const { default: sut } = await import("/slothsoft@farah/js/XSLT");
            
    return "" + sut.transformToFragment;
}
            
import("/slothsoft@farah/js/Test").then(Test => Test.run(test, arguments));
EOT, $arguments);
        
        $this->assertThat($actual, new StringStartsWith('function'));
    }
    
    /**
     *
     * @dataProvider provideTransformations
     */
    public function test_transformToFragment_matchesChildCount(string $data, string $template): void {
        $arguments = [
            $data,
            $template
        ];
        
        $actual = $this->client->executeAsyncScript(<<<EOT
async function test(data, template) {
    const { default: sut } = await import("/slothsoft@farah/js/XSLT");

    var result = sut.transformToFragment(data, template, document);

    return result.childNodes.length;
}

import("/slothsoft@farah/js/Test").then(Test => Test.run(test, arguments));
EOT, $arguments);
        
        $dom = new DOMHelper();
        $expected = $dom->transformToFragment($data, $template);
        $expected = $expected->childNodes->length;
        
        $this->assertThat($actual, new IsEqual($expected));
    }
    
    /**
     *
     * @dataProvider provideTransformations
     */
    public function test_transformToFragment_matchesXML(string $data, string $template): void {
        $arguments = [
            $data,
            $template
        ];
        
        $actual = $this->client->executeAsyncScript(<<<EOT
async function test(data, template) {
    const { default: sut } = await import("/slothsoft@farah/js/XSLT");
            
    var result = sut.transformToFragment(data, template, document);
            
    const { default: DOM } = await import("/slothsoft@farah/js/DOM");
            
    return DOM.saveXML(result);
}
            
import("/slothsoft@farah/js/Test").then(Test => Test.run(test, arguments));
EOT, $arguments);
        
        $dom = new DOMHelper();
        $actual = $dom->parse($actual);
        $expected = $dom->transformToFragment($data, $template);
        
        $this->assertThat($actual, new DOMNodeEqualTo($expected));
    }
    
    /**
     *
     * @dataProvider provideTransformations
     */
    public function test_transformToFragmentAsync_matchesXML(string $data, string $template): void {
        $arguments = [
            $data,
            $template
        ];
        
        $actual = $this->client->executeAsyncScript(<<<EOT
async function test(data, template) {
    const { default: sut } = await import("/slothsoft@farah/js/XSLT");
            
    var result = await sut.transformToFragmentAsync(data, template, document);
            
    const { default: DOM } = await import("/slothsoft@farah/js/DOM");
            
    return DOM.saveXML(result);
}
            
import("/slothsoft@farah/js/Test").then(Test => Test.run(test, arguments));
EOT, $arguments);
        
        $dom = new DOMHelper();
        $actual = $dom->parse($actual);
        $expected = $dom->transformToFragment($data, $template);
        
        $this->assertThat($actual, new DOMNodeEqualTo($expected));
    }
    
    public function provideTransformations(): iterable {
        yield 'html' => [
            'farah://slothsoft@farah/',
            'farah://slothsoft@farah/xsl/html'
        ];
        
        yield 'import' => [
            'farah://slothsoft@farah/',
            'farah://slothsoft@test-module/xsl/import'
        ];
    }
}