<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\API\JavaScript;

use PHPUnit\Framework\Constraint\IsTrue;
use Slothsoft\FarahTesting\FarahServerTestCase;
use Slothsoft\Farah\FarahUrl\FarahUrlAuthority;

final class BootstrapTest extends FarahServerTestCase {
    
    protected static function setUpServer(): void {
        self::$server->setModule(FarahUrlAuthority::createFromVendorAndModule('slothsoft', 'test-module'), 'test-files/test-module');
    }
    
    protected function setUpClient(): void {
        $this->client->request('GET', '/slothsoft@farah/example-page');
    }
    
    /**
     */
    public function test_run(): void {
        $arguments = [];
        
        $actual = $this->client->executeAsyncScript(<<<EOT
async function test() {
    const { default: sut } = await import("/slothsoft@farah/js/Bootstrap");

    let hasRun = false;
    sut.run(() => hasRun = true);

    return hasRun;
}
            
import("/slothsoft@farah/js/Test").then(Test => Test.run(test, arguments));
EOT, $arguments);
        
        $this->assertThat($actual, new IsTrue());
    }
}