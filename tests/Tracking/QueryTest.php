<?php
declare(strict_types = 1);

namespace Slothsoft\Farah\Tracking;

use ArrayIterator;
use mysqli;
use mysqli_result;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;
use RuntimeException;
use Slothsoft\Core\DBMS\Client;
use Slothsoft\Core\DBMS\Manager;

final class QueryTest extends TestCase {
    
    protected function setUp(): void {
        if (! extension_loaded('mysqli')) {
            $this->markTestSkipped('Tracking database tests require mysqli.');
        }
    }
    
    /** @return iterable<string, array{string}> */
    public function readOperations(): iterable {
        foreach (['select', 'group', 'count', 'rollback', 'current', 'outdated', 'parse', 'backup', 'import'] as $operation) {
            yield $operation => [$operation];
        }
    }
    
    /**
     * @dataProvider readOperations
     * @runInSeparateProcess
     */
    public function testFailedReadThrows(string $operation): void {
        $this->configureConnection([false]);
        $this->expectException(RuntimeException::class);
        $table = in_array($operation, ['current', 'outdated', 'parse', 'backup'], true) ? 'temp' : ($operation === 'import' ? 'access_log' : 'log_page');
        $this->expectExceptionMessage('Failed to read tracking table "' . $table . '".');
        $this->read($operation);
    }
    
    /**
     * @dataProvider readOperations
     * @runInSeparateProcess
     */
    public function testEmptyResultIsSuccessful(string $operation): void {
        $this->configureConnection([$this->result($operation === 'count' ? [['count' => 0]] : [])]);
        $expected = match ($operation) {
            'count', 'rollback', 'parse', 'import' => 0,
            'backup' => null,
            default => []
        };
        $this->assertSame($expected, $this->read($operation));
    }
    
    /** @return iterable<string, array{string}> */
    public function batchOperations(): iterable {
        yield 'parse' => ['parse'];
        yield 'backup' => ['backup'];
    }
    
    /**
     * @dataProvider batchOperations
     * @runInSeparateProcess
     */
    public function testFailedBatchReadDoesNotWriteOrAdvance(string $operation): void {
        // The ID lookup succeeds, but fetching those records fails. No update or delete may follow.
        $this->configureConnection([$this->result([['id' => 42]]), false]);
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Failed to read tracking table "temp".');
        $this->read($operation);
    }
    
    /** @runInSeparateProcess */
    public function testMissingConnectionConfigurationThrows(): void {
        Client::clearDefaultAuthority();
        putenv(Client::ENV_CONNECTION_PASSWORD . '=');
        $archive = new Archive(Manager::getDatabase('tracking_test'));
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Failed to read tracking table "temp".');
        $archive->getCurrent();
    }
    
    /** @param list<mysqli_result|false> $results */
    private function configureConnection(array $results): void {
        $connection = $this->createMock(mysqli::class);
        $connection->method('ping')->willReturn(true);
        $connection->expects($this->exactly(count($results)))->method('query')->with($this->stringStartsWith('SELECT '))->willReturnOnConsecutiveCalls(...$results);
        $client = Manager::getClient();
        (new ReflectionProperty(Client::class, 'sqli'))->setValue($client, $connection);
        (new ReflectionProperty(Client::class, 'connected'))->setValue($client, true);
    }
    
    /** @param list<array<string, mixed>> $rows */
    private function result(array $rows): mysqli_result {
        $result = $this->createMock(mysqli_result::class);
        $result->method('fetch_all')->willReturn($rows);
        $result->method('getIterator')->willReturn(new ArrayIterator($rows));
        return $result;
    }
    
    private function read(string $operation): mixed {
        $archive = new Archive(Manager::getDatabase('tracking_test'));
        $log = $archive->getLogTableByURI('/getPage.php');
        return match ($operation) {
            'select' => $log->select([], '', 0, 10),
            'group' => $log->selectGroup('SERVER_NAME', [], ''),
            'count' => $log->selectCount([], ''),
            'rollback' => $log->fixRollback(Manager::getTable('tracking_test', 'temp')),
            'current' => $archive->getCurrent(),
            'outdated' => $archive->getOutdated(),
            'parse' => $archive->parse(),
            'backup' => $archive->backup(),
            'import' => $archive->import(),
            default => throw new RuntimeException('Unknown test operation.')
        };
    }
}
