<?php
declare(strict_types = 1);

namespace Slothsoft\Farah\ResponseStrategy;

use Exception;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

final class StreamingHttpTest extends TestCase {
    
    /**
     * @dataProvider bufferProvider
     * @runInSeparateProcess
     * @throws Exception
     */
    public function testClientReceivesChunksBeforeProducerFinishes(string $query, bool $uppercase): void {
        $gate = temp_dir(__CLASS__);
        [$server, $port] = $this->startServer($gate);
        $client = null;
        try {
            $client = fopen("http://127.0.0.1:$port/test@streaming/events$query", 'rb', false, stream_context_create([
                'http' => ['timeout' => 3]
            ]));
            $this->assertIsResource($client);
            stream_set_timeout($client, 3);
            $prefix = $query === '?unbuffered=1' ? '' : 'prefix:';
            $firstLine = $prefix . 'event:start';
            $this->assertSame($uppercase ? strtoupper($firstLine) : $firstLine, rtrim(fgets($client)));
            $this->assertSame($uppercase ? "DATA:\n" : "data:\n", fgets($client));
            $this->assertSame("\n", fgets($client));
            $this->assertFileDoesNotExist($gate . '/completed');
            touch($gate . '/next');
            $this->assertSame($uppercase ? "EVENT:NEXT\n" : "event:next\n", fgets($client));
            $this->assertSame($uppercase ? "DATA:SECOND\n" : "data:second\n", fgets($client));
            $this->assertSame("\n", fgets($client));
            $this->assertFileDoesNotExist($gate . '/completed');
            touch($gate . '/finish');
            $this->assertSame('', stream_get_contents($client));
            clearstatcache();
            $this->assertFileExists($gate . '/completed');
            $this->assertBufferStackPreserved($gate);
            $this->assertStringContainsString('text/plain', implode('\n', $http_response_header));
            $this->assertStringContainsString('X-Accel-Buffering: no', implode('\n', $http_response_header));
        } finally {
            touch($gate . '/next');
            touch($gate . '/finish');
            if (is_resource($client)) {
                fclose($client);
            }
            $server->stop(1);
        }
        $this->assertStringNotContainsString('Fatal error', $server->getErrorOutput());
    }
    
    public function bufferProvider(): iterable {
        yield 'PHP output_buffering=4096' => ['', false];
        yield 'non-removable flushable buffer' => ['?nonremovable=1', false];
        yield 'custom flushable handler remains active' => ['?custom=1', true];
        yield 'no PHP output buffer' => ['?unbuffered=1', false];
    }
    
    /**
     * @runInSeparateProcess
     * @throws Exception
     */
    public function testOrdinaryResponseKeepsOutputHandlers(): void {
        $gate = temp_dir(__CLASS__);
        [$server, $port] = $this->startServer($gate);
        try {
            $body = file_get_contents("http://127.0.0.1:$port/test@streaming/events?buffered=1", false, stream_context_create([
                'http' => ['timeout' => 3]
            ]));
            $this->assertSame("PREFIX:EVENT:START\nDATA:\n\nEVENT:NEXT\nDATA:SECOND\n\n", $body);
            $this->assertStringNotContainsString('X-Accel-Buffering', implode('\n', $http_response_header));
            $this->assertFileExists($gate . '/completed');
            $this->assertBufferStackPreserved($gate);
        } finally {
            $server->stop(1);
        }
    }
    
    /**
     * @runInSeparateProcess
     * @dataProvider unsupportedBufferProvider
     * @throws Exception
     */
    public function testUnsupportedBufferTerminatesAndPreservesContent(string $query): void {
        $gate = temp_dir(__CLASS__);
        [$server, $port] = $this->startServer($gate);
        try {
            $body = file_get_contents("http://127.0.0.1:$port/test@streaming/events$query", false, stream_context_create([
                'http' => ['timeout' => 3]
            ]));
            $this->assertSame("PREFIX:EVENT:START\nDATA:\n\nEVENT:NEXT\nDATA:SECOND\n\n", $body);
            $this->assertFileExists($gate . '/completed');
            $this->assertBufferStackPreserved($gate);
        } finally {
            $server->stop(1);
        }
    }

    public function unsupportedBufferProvider(): iterable {
        yield 'non-flushable handler' => ['?blocked=1'];
        yield 'outer buffer retains flushed inner output' => ['?nested=1'];
    }

    private function assertBufferStackPreserved(string $gate): void {
        $levels = explode(':', file_get_contents($gate . '/buffer-levels'));
        $this->assertCount(2, $levels);
        $this->assertSame($levels[0], $levels[1]);
    }
    
    private function startServer(string $gate): array {
        $socket = stream_socket_server('tcp://127.0.0.1:0');
        $this->assertIsResource($socket);
        $port = (int) substr(strrchr(stream_socket_get_name($socket, false), ':'), 1);
        fclose($socket);
        $root = dirname(__DIR__, 2);
        $server = new Process([
            PHP_BINARY, '-d', 'output_buffering=4096', '-d', 'zlib.output_compression=0',
            '-S', "127.0.0.1:$port", $root . '/test-files/http-streaming/router.php'
        ], $root, ['FARAH_STREAM_GATE' => $gate]);
        $server->start();
        $deadline = microtime(true) + 5;
        do {
            $connection = @stream_socket_client("tcp://127.0.0.1:$port", $errorCode, $errorMessage, 0.1);
            if (is_resource($connection)) {
                fclose($connection);
                return [$server, $port];
            }
            usleep(10_000);
        } while ($server->isRunning() && microtime(true) < $deadline);
        $server->stop(1);
        $this->fail('HTTP server did not start: ' . $server->getErrorOutput());
    }
}
