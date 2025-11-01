<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\API\JavaScript;

use PHPUnit\Framework\Constraint\IsEqual;
use PHPUnit\Framework\Constraint\IsNull;
use PHPUnit\Framework\Constraint\LogicalNot;
use Slothsoft\FarahTesting\FarahServerTestCase;
use Slothsoft\Farah\FarahUrl\FarahUrlAuthority;
use PHPUnit\Framework\Constraint\IsTrue;

final class BlobDatabaseTest extends FarahServerTestCase {
    
    protected static function setUpServer(): void {
        self::$server->setModule(FarahUrlAuthority::createFromVendorAndModule('slothsoft', 'test-module'), 'test-files/test-module');
    }
    
    protected function setUpClient(): void {
        $this->client->request('GET', '/slothsoft@farah/example-page');
    }
    
    public function test_lookupBlobTime(): void {
        $arguments = [];
        
        $actual = $this->client->executeAsyncScript(<<<EOT
async function test() {
    const { default: BlobDatabase } = await import("/slothsoft@farah/js/BlobDatabase");

    const sut = new BlobDatabase("db");

    await sut.initializeAsync();

    return sut.lookupTime;
}
            
import("/slothsoft@farah/js/Test").then(Test => Test.run(test, arguments));
EOT, $arguments);
        
        $this->assertThat($actual, new IsEqual(1000 * time(), 1000 * 60));
    }
    
    /**
     *
     * @dataProvider provideTestBlobs
     */
    public function test_lookupBlobAsync_missing(string $url): void {
        $arguments = [
            $url
        ];
        
        $actual = $this->client->executeAsyncScript(<<<EOT
async function test(url) {
    const { default: BlobDatabase } = await import("/slothsoft@farah/js/BlobDatabase");

    const sut = new BlobDatabase("db");

    await sut.initializeAsync();

    const actual = await sut.lookupBlobAsync(url);

    return actual.blob;
}
            
import("/slothsoft@farah/js/Test").then(Test => Test.run(test, arguments));
EOT, $arguments);
        
        $this->assertThat($actual, new LogicalNot(new IsNull()));
    }
    
    /**
     *
     * @dataProvider provideTestBlobs
     */
    public function test_lookupBlobAsync_twice(string $url): void {
        $arguments = [
            $url
        ];
        
        $actual = $this->client->executeAsyncScript(<<<EOT
async function test(url) {
    const { default: BlobDatabase } = await import("/slothsoft@farah/js/BlobDatabase");
            
    const sut = new BlobDatabase("db");
            
    await sut.initializeAsync();
            
    const actual1 = await sut.lookupBlobAsync(url);
    const actual2 = await sut.lookupBlobAsync(url);
            
    return actual1.etag === actual2.etag;
}
            
import("/slothsoft@farah/js/Test").then(Test => Test.run(test, arguments));
EOT, $arguments);
        
        $this->assertThat($actual, new IsTrue());
    }
    
    public function provideTestBlobs(): iterable {
        yield 'url' => [
            '/slothsoft@farah/'
        ];
    }
    
    /**
     *
     * @dataProvider provideTestObjects
     */
    public function test_insertBlob(array $obj): void {
        $arguments = [
            $obj
        ];
        
        $actual = $this->client->executeAsyncScript(<<<EOT
async function test(obj) {
    const { default: BlobDatabase } = await import("/slothsoft@farah/js/BlobDatabase");
            
    const sut = new BlobDatabase("db");
            
    await sut.initializeAsync();

    const actual = await new Promise(resolve => sut.insertBlob(obj, resolve));
            
    return actual;
}
            
import("/slothsoft@farah/js/Test").then(Test => Test.run(test, arguments));
EOT, $arguments);
        
        $this->assertThat($actual, new IsEqual(true));
    }
    
    public function provideTestObjects(): iterable {
        yield 'object' => [
            [
                'url' => '/',
                'blob' => 'test',
                'etag' => '',
                'lastModified' => time(),
                'lookupTime' => time()
            ]
        ];
    }
}