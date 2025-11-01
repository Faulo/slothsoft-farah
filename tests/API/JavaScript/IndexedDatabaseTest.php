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
    
    public function test_getObjectByIdAsync(): void {
        $arguments = [];
        
        $actual = $this->client->executeAsyncScript(<<<EOT
async function test() {
    const { default: IndexedDatabase } = await import("/slothsoft@farah/js/IndexedDatabase");

    const sut = new IndexedDatabase("test", 1, indexed => {
        const imageStore = indexed.db.createObjectStore(
            "test",
            { keyPath: "id" }
        );
        imageStore.createIndex(
            "id-index",
            "id",
            { unique: true }
        );
    });

    await sut.initializeAsync();

    const actual = await sut.getObjectByIdAsync("test", "id-index", 1);

    return actual;
}
            
import("/slothsoft@farah/js/Test").then(Test => Test.run(test, arguments));
EOT, $arguments);
        
        $this->assertThat($actual, new IsEqual(null));
    }
    
    /**
     *
     * @dataProvider provideTestObjects
     */
    public function test_putObjectAsync(array $obj): void {
        $arguments = [
            $obj
        ];
        
        $actual = $this->client->executeAsyncScript(<<<EOT
async function test(obj) {
    const { default: IndexedDatabase } = await import("/slothsoft@farah/js/IndexedDatabase");
            
    const sut = new IndexedDatabase("test", 1, indexed => {
        const imageStore = indexed.db.createObjectStore(
            "test",
            { keyPath: "id" }
        );
        imageStore.createIndex(
            "id-index",
            "id",
            { unique: true }
        );
    });
            
    await sut.initializeAsync();

    await sut.putObjectAsync("test", obj);
            
    const actual = await sut.getObjectByIdAsync("test", "id-index", obj.id);
            
    return actual;
}
            
import("/slothsoft@farah/js/Test").then(Test => Test.run(test, arguments));
EOT, $arguments);
        
        $this->assertThat($actual, new IsEqual($obj));
    }
    
    /**
     *
     * @dataProvider provideTestObjects
     */
    public function test_getObjectCursorAsync(array $obj): void {
        $arguments = [
            $obj
        ];
        
        $actual = $this->client->executeAsyncScript(<<<EOT
async function test(obj) {
    const { default: IndexedDatabase } = await import("/slothsoft@farah/js/IndexedDatabase");

    const sut = new IndexedDatabase("test", 1, indexed => {
        const imageStore = indexed.db.createObjectStore(
            "test",
            { keyPath: "id" }
        );
        imageStore.createIndex(
            "id-index",
            "id",
            { unique: true }
        );
    });

    await sut.initializeAsync();

    await sut.putObjectAsync("test", obj);

    const actual = await sut.getObjectCursorAsync("test");

    return actual.value;
}
            
import("/slothsoft@farah/js/Test").then(Test => Test.run(test, arguments));
EOT, $arguments);
        
        $this->assertThat($actual, new IsEqual($obj));
    }
    
    /**
     *
     * @dataProvider provideTestObjects
     */
    public function test_deleteObjectAsync(array $obj): void {
        $arguments = [
            $obj
        ];
        
        $actual = $this->client->executeAsyncScript(<<<EOT
async function test(obj) {
    const { default: IndexedDatabase } = await import("/slothsoft@farah/js/IndexedDatabase");
            
    const sut = new IndexedDatabase("test", 1, indexed => {
        const imageStore = indexed.db.createObjectStore(
            "test",
            { keyPath: "id" }
        );
        imageStore.createIndex(
            "id-index",
            "id",
            { unique: true }
        );
    });
            
    await sut.initializeAsync();
            
    await sut.putObjectAsync("test", obj);

    await sut.deleteObjectAsync("test", obj.id);
            
    const actual = await sut.getObjectCursorAsync("test");
            
    return actual;
}
            
import("/slothsoft@farah/js/Test").then(Test => Test.run(test, arguments));
EOT, $arguments);
        
        $this->assertThat($actual, new IsEqual(null));
    }
    
    public function provideTestObjects(): iterable {
        yield 'object' => [
            [
                'id' => 2
            ]
        ];
        
        yield 'object with name' => [
            [
                'id' => 3,
                'name' => 'object-with-name'
            ]
        ];
    }
}