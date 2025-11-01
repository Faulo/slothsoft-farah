<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\API\JavaScript;

use PHPUnit\Framework\Constraint\IsEqual;
use Slothsoft\FarahTesting\FarahServerTestCase;
use Slothsoft\Farah\FarahUrl\FarahUrlAuthority;

final class IndexedDatabaseTest extends FarahServerTestCase {
    
    protected static function setUpServer(): void {
        self::$server->setModule(FarahUrlAuthority::createFromVendorAndModule('slothsoft', 'test-module'), 'test-files/test-module');
    }
    
    protected function setUpClient(): void {
        $this->client->request('GET', '/slothsoft@farah/example-page');
    }
    
    public function test_dbInitialized(): void {
        $arguments = [];
        
        $actual = $this->client->executeAsyncScript(<<<EOT
async function test(uri) {
    const { default: IndexedDatabase } = await import("/slothsoft@farah/js/IndexedDatabase");

    const sut = new IndexedDatabase("db");

    await sut.initializeAsync();

    return sut.dbInitialized;
}
            
import("/slothsoft@farah/js/Test").then(Test => Test.run(test, arguments));
EOT, $arguments);
        
        $this->assertThat($actual, new IsEqual(true));
    }
    
    public function test_dbName(): void {
        $dbName = 'test';
        
        $arguments = [
            $dbName
        ];
        
        $actual = $this->client->executeAsyncScript(<<<EOT
async function test(dbName) {
    const { default: IndexedDatabase } = await import("/slothsoft@farah/js/IndexedDatabase");
            
    const sut = new IndexedDatabase(dbName);

    await sut.initializeAsync();
            
    return sut.dbName;
}
            
import("/slothsoft@farah/js/Test").then(Test => Test.run(test, arguments));
EOT, $arguments);
        
        $this->assertThat($actual, new IsEqual($dbName));
    }
    
    public function test_dbVersion(): void {
        $dbVersion = 3;
        
        $arguments = [
            $dbVersion
        ];
        
        $actual = $this->client->executeAsyncScript(<<<EOT
async function test(dbVersion) {
    const { default: IndexedDatabase } = await import("/slothsoft@farah/js/IndexedDatabase");
            
    const sut = new IndexedDatabase("test", dbVersion);

    await sut.initializeAsync();
            
    return sut.dbVersion;
}
            
import("/slothsoft@farah/js/Test").then(Test => Test.run(test, arguments));
EOT, $arguments);
        
        $this->assertThat($actual, new IsEqual($dbVersion));
    }
}