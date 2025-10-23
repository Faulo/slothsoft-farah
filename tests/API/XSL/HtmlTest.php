<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\API\XSL;

use PHPUnit\Framework\Constraint\IsEqual;
use Slothsoft\FarahTesting\FarahServerTestCase;
use Slothsoft\Farah\FarahUrl\FarahUrlAuthority;

final class HtmlTest extends FarahServerTestCase {
    
    protected static function setUpServer(): void {
        self::$server->setModule(FarahUrlAuthority::createFromVendorAndModule('slothsoft', 'test-module'), 'test-files/test-module');
    }
    
    protected function setUpClient() {
        $this->client->request('GET', '/slothsoft@test-module/tests/html');
    }
    
    public function test_titleIsUrl(): void {
        $actual = $this->client->executeScript(<<<EOT
return document.querySelector("title").textContent;
EOT);
        
        $this->assertThat($actual, new IsEqual('farah://slothsoft@test-module/tests/html'));
    }
}