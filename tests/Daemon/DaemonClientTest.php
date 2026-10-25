<?php
declare(strict_types = 1);

namespace Slothsoft\Farah\Daemon;

use PHPUnit\Framework\TestCase;

/**
 * DaemonClientTest
 *
 * @see DaemonClient
 *
 */
final class DaemonClientTest extends TestCase {

    /** @runInSeparateProcess */
    public function testReadsResponseUntilPeerCloses(): void {
        $process = proc_open([PHP_BINARY, __DIR__ . '/response-server.php'], [
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
            $this->assertSame(['hello'], iterator_to_array($client->call('hello')));
        } finally {
            proc_terminate($process);
            foreach ($pipes as $pipe) {
                fclose($pipe);
            }
            proc_close($process);
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
