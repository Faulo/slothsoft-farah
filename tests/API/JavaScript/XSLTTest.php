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
        $this->client->request('GET', '/slothsoft@test-module/tests/xslt');
    }
    
    public function test_transformToFragment_exists(): void {
        $actual = $this->client->executeScript(<<<EOT
return XSLT.transformToFragment.toString();
EOT);
        
        $this->assertThat($actual, new StringStartsWith('function'));
    }
}