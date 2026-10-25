<?php
declare(strict_types = 1);

namespace Slothsoft\Farah\Daemon;

use PHPUnit\Framework\TestCase;
use Slothsoft\Farah\FarahUrl\FarahUrlArguments;

/**
 * AbstractDaemonServerTest
 *
 * @see AbstractDaemonServer
 *
 */
final class AbstractDaemonServerTest extends TestCase {
    
    /** @runInSeparateProcess */
    public function testHandlesSocketObjects(): void {
        $server = new class(0) extends AbstractDaemonServer {
            public function onInitialize(FarahUrlArguments $args): void {
            }
            
            public function onMessage($message): iterable {
                $this->stop();
                yield static::STDOUT => $message;
            }
        };
        $server->init(FarahUrlArguments::createEmpty());
        $socket = (new \ReflectionProperty(AbstractDaemonServer::class, 'socket'))->getValue($server);
        socket_getsockname($socket, $address, $port);
        $client = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        try {
            socket_set_option($client, SOL_SOCKET, SO_RCVTIMEO, ['sec' => 5, 'usec' => 0]);
            $this->assertTrue(socket_connect($client, $address, $port));
            socket_write($client, '"hello"');
            
            $this->assertSame([], iterator_to_array($server->toChunks()));
            $response = '';
            while (($chunk = socket_read($client, 65535)) !== false && $chunk !== '') {
                $response .= $chunk;
            }
            $this->assertSame('"hello"' . "\0", $response);
        } finally {
            socket_close($client);
            socket_close($socket);
        }
    }
    
    /**
     *
     * @test
     */
    public function testClassExists(): void {
        $this->assertTrue(class_exists(AbstractDaemonServer::class), "Failed to load class 'Slothsoft\Farah\Daemon\AbstractDaemonServer'!");
    }
}
