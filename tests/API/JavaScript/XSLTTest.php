<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\API\JavaScript;

use PHPUnit\Framework\Constraint\StringStartsWith;
use Slothsoft\FarahTesting\FarahServerTestCase;
use Slothsoft\Farah\FarahUrl\FarahUrlAuthority;

final class XSLTTest extends FarahServerTestCase {
    
    protected static function setUpServer(): void {
        self::$server->setModule(FarahUrlAuthority::createFromVendorAndModule('slothsoft', 'test-module'), 'test-files/test-module');
    }
    
    protected function setUpClient(): void {
        $this->client->request('GET', '/');
    }
    
    public function test_transformToFragment_exists(): void {
        $arguments = [];
        
        $actual = $this->client->executeAsyncScript(<<<EOT
async function test(uri) {
    const { default: sut } = await import("/slothsoft@farah/js/XSLT");
            
    return "" + sut.transformToFragment;
}
            
import("/slothsoft@farah/js/Test").then(Test => Test.run(test, arguments));
EOT, $arguments);
        
        $this->assertThat($actual, new StringStartsWith('function'));
    }
}