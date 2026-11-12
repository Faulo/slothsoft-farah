<?php
declare(strict_types = 1);

namespace Slothsoft\Farah\Daemon;

use LogicException;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;
use RuntimeException;
use Slothsoft\Farah\FarahUrl\FarahUrlArguments;
use Socket;

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
            
            public function onMessage(mixed $message): iterable {
                $this->stop();
                yield static::STDOUT => $message;
            }
        };
        $server->init(FarahUrlArguments::createEmpty());
        $socket = (new \ReflectionProperty(AbstractDaemonServer::class, 'socket'))->getValue($server);
        $this->assertInstanceOf(Socket::class, $socket);
        $this->assertTrue(socket_getsockname($socket, $address, $port));
        $client = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        $this->assertInstanceOf(Socket::class, $client);
        try {
            $this->assertTrue(socket_set_option($client, SOL_SOCKET, SO_RCVTIMEO, ['sec' => 5, 'usec' => 0]));
            $this->assertTrue(socket_connect($client, $address, $port));
            $this->assertSame(7, socket_write($client, '"hello"'));
            
            $this->assertSame([], iterator_to_array($server->toChunks()));
            $response = '';
            while (($chunk = socket_read($client, 65535)) !== false && $chunk !== '') {
                $response .= $chunk;
            }
            $this->assertSame('"hello"' . "\0", $response);
        } finally {
            socket_close($client);
            unset($server);
        }
    }
    
    private function createServer(int $port = 0, bool $invalidJSON = false): AbstractDaemonServer {
        return new class($port, $invalidJSON) extends AbstractDaemonServer {
            public function __construct(int $port, private bool $invalidJSON) {
                parent::__construct($port);
            }
            
            public function onInitialize(FarahUrlArguments $args): void {
            }
            
            public function onMessage(mixed $message): iterable {
                $this->stop();
                yield static::STDERR => 'handled';
                yield static::STDOUT => $this->invalidJSON ? "\xff" : $message;
            }
        };
    }
    
    private function listener(AbstractDaemonServer $server): Socket {
        $socket = (new ReflectionProperty(AbstractDaemonServer::class, 'socket'))->getValue($server);
        $this->assertInstanceOf(Socket::class, $socket);
        return $socket;
    }
    
    private function connectTo(AbstractDaemonServer $server): Socket {
        $this->assertTrue(socket_getsockname($this->listener($server), $address, $port));
        $client = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        $this->assertInstanceOf(Socket::class, $client);
        $this->assertTrue(socket_set_option($client, SOL_SOCKET, SO_RCVTIMEO, ['sec' => 5, 'usec' => 0]));
        $this->assertTrue(socket_connect($client, $address, $port));
        $this->assertSame(7, socket_write($client, '"hello"'));
        return $client;
    }
    
    /** @runInSeparateProcess */
    public function testEncodingFailureUsesErrorOutputAndClosesClient(): void {
        $server = $this->createServer(invalidJSON: true);
        $server->init(FarahUrlArguments::createEmpty());
        $client = $this->connectTo($server);
        try {
            $errors = iterator_to_array($server->toChunks());
            $this->assertSame('handled', $errors[0]);
            $this->assertStringContainsString('Malformed UTF-8', $errors[1]);
            $this->assertSame("\0", socket_read($client, 65535));
            $this->assertSame('', socket_read($client, 65535));
        } finally {
            socket_close($client);
        }
    }
    
    /** @runInSeparateProcess */
    public function testAbandonedGeneratorClosesAcceptedClient(): void {
        $server = $this->createServer();
        $server->init(FarahUrlArguments::createEmpty());
        $client = $this->connectTo($server);
        try {
            $chunks = $server->toChunks();
            $this->assertSame('handled', $chunks->current());
            unset($chunks);
            $this->assertSame('', socket_read($client, 65535));
        } finally {
            socket_close($client);
        }
    }
    
    /** @runInSeparateProcess */
    public function testBindFailureThrowsAndCanBeRetried(): void {
        $first = $this->createServer();
        $first->init(FarahUrlArguments::createEmpty());
        $this->assertTrue(socket_getsockname($this->listener($first), $address, $port));
        $second = $this->createServer($port);
        try {
            $second->init(FarahUrlArguments::createEmpty());
            $this->fail('Binding an occupied port must fail.');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('bind daemon', $e->getMessage());
        }
        unset($first);
        $second->init(FarahUrlArguments::createEmpty());
        $this->assertTrue(socket_getsockname($this->listener($second), $newAddress, $newPort));
        $this->assertSame($port, $newPort);
    }
    
    /** @runInSeparateProcess */
    public function testInitializationFailureReleasesListener(): void {
        $server = new class(0) extends AbstractDaemonServer {
            public int $boundPort;
            
            public function onInitialize(FarahUrlArguments $args): void {
                $socket = (new ReflectionProperty(AbstractDaemonServer::class, 'socket'))->getValue($this);
                if (! socket_getsockname($socket, $address, $port)) {
                    throw new RuntimeException('Cannot inspect test listener.');
                }
                $this->boundPort = $port;
                throw new RuntimeException('Initialization failed');
            }
            
            public function onMessage(mixed $message): iterable {
                return [];
            }
        };
        try {
            $server->init(FarahUrlArguments::createEmpty());
            $this->fail('Initialization must fail.');
        } catch (RuntimeException $e) {
            $this->assertSame('Initialization failed', $e->getMessage());
        }
        $probe = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        $this->assertInstanceOf(Socket::class, $probe);
        try {
            $this->assertTrue(socket_bind($probe, '127.0.0.1', $server->boundPort));
        } finally {
            socket_close($probe);
        }
    }
    
    /** @runInSeparateProcess */
    public function testReadFailureThrows(): void {
        $server = $this->createServer();
        $server->init(FarahUrlArguments::createEmpty());
        $client = $this->connectTo($server);
        try {
            $this->assertTrue(socket_set_option($client, SOL_SOCKET, SO_RCVTIMEO, ['sec' => 0, 'usec' => 100000]));
            $this->expectException(RuntimeException::class);
            $this->expectExceptionMessage('read daemon socket');
            SocketIO::read($client);
        } finally {
            socket_close($client);
        }
    }
    
    /** @runInSeparateProcess */
    public function testRequiresInitialization(): void {
        $server = $this->createServer();
        $this->expectException(LogicException::class);
        iterator_to_array($server->toChunks());
    }
    
    /**
     *
     * @test
     */
    public function testClassExists(): void {
        $this->assertTrue(class_exists(AbstractDaemonServer::class), "Failed to load class 'Slothsoft\Farah\Daemon\AbstractDaemonServer'!");
    }
}
