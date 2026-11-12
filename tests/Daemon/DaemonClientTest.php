<?php
declare(strict_types = 1);

namespace Slothsoft\Farah\Daemon;

use JsonException;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Socket;

/**
 * DaemonClientTest
 *
 * @see DaemonClient
 *
 */
final class DaemonClientTest extends TestCase {
    
    /** @return iterable<string, array{string, string, mixed}> */
    public static function requests(): iterable {
        yield 'echo until EOF' => ['', 'hello', 'hello'];
        yield 'large request' => ['length', str_repeat('x', 3 * 1024 * 1024), 3 * 1024 * 1024];
    }
    
    /**
     * @runInSeparateProcess
     * @dataProvider requests
     */
    public function testReadsResponseUntilPeerCloses(string $mode, string $request, mixed $expected): void {
        $process = proc_open([PHP_BINARY, __DIR__ . '/response-server.php', $mode], [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w']
        ], $pipes);
        $this->assertIsResource($process);
        try {
            stream_set_timeout($pipes[1], 5);
            $port = (int) fgets($pipes[1]);
            $this->assertGreaterThan(0, $port);
            
            $client = new DaemonClient($port);
            $this->assertSame([$expected], iterator_to_array($client->call($request)));
        } finally {
            proc_terminate($process);
            foreach ($pipes as $pipe) {
                fclose($pipe);
            }
            proc_close($process);
        }
    }
    
    /** @runInSeparateProcess */
    public function testInvalidJSONFailsBeforeConnecting(): void {
        $client = new DaemonClient(0);
        $this->expectException(JsonException::class);
        iterator_to_array($client->call("\xff"));
    }
    
    /** @runInSeparateProcess */
    public function testRefusedConnectionThrows(): void {
        $reserved = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        $this->assertInstanceOf(Socket::class, $reserved);
        try {
            $this->assertTrue(socket_bind($reserved, '127.0.0.1', 0));
            $this->assertTrue(socket_getsockname($reserved, $address, $port));
            $this->expectException(RuntimeException::class);
            $this->expectExceptionMessage('connect to daemon');
            iterator_to_array((new DaemonClient($port))->call('hello'));
        } finally {
            socket_close($reserved);
        }
    }
    
    /**
     *
     * @test
     */
    public function testClassExists(): void {
        $this->assertTrue(class_exists(DaemonClient::class), "Failed to load class 'Slothsoft\Farah\Daemon\DaemonClient'!");
    }
}
