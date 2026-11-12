<?php
declare(strict_types = 1);

namespace Slothsoft\Farah\Daemon;

use BadMethodCallException;
use Generator;
use LogicException;
use RuntimeException;
use Slothsoft\Core\Calendar\DateTimeFormatter;
use Slothsoft\Core\IO\Writable\ChunkWriterInterface;
use Slothsoft\Farah\FarahUrl\FarahUrlArguments;
use Socket;
use Throwable;

/**
 * Base class for socket-backed Farah daemon servers that process chunk writer requests.
 *
 * @author Daniel Schulz
 * @since 2021-01-01
 * @deprecated Included for historical compatibility only. This API is deprecated and should not be used in new code.
 */
abstract class AbstractDaemonServer implements ChunkWriterInterface {
    
    public const DAEMON_DOMAIN = AF_INET;
    
    public const DAEMON_TYPE = SOCK_STREAM;
    
    public const DAEMON_PROTOCOL = SOL_TCP;
    
    public const DAEMON_ADDRESS = '127.0.0.1';
    
    public const STDOUT = 1;
    
    public const STDERR = 2;
    
    private int $port;
    
    private ?Socket $socket = null;
    
    private bool|null $isRunning = null;
    
    public function __construct(int $port) {
        $this->port = $port;
    }
    
    public function __destruct() {
        $this->closeSocket();
    }
    
    private function closeSocket(): void {
        if ($this->socket !== null) {
            socket_close($this->socket);
            $this->socket = null;
        }
    }
    
    public function init(FarahUrlArguments $args): void {
        $this->closeSocket();
        $socket = SocketIO::create(static::DAEMON_DOMAIN, static::DAEMON_TYPE, static::DAEMON_PROTOCOL);
        try {
            if (! @socket_bind($socket, static::DAEMON_ADDRESS, $this->port)) {
                throw SocketIO::failure(sprintf('bind daemon at %s:%d', static::DAEMON_ADDRESS, $this->port), $socket);
            }
            if (! @socket_listen($socket)) {
                throw SocketIO::failure('listen on daemon socket', $socket);
            }
            if (! @socket_set_nonblock($socket)) {
                throw SocketIO::failure('set daemon socket nonblocking', $socket);
            }
            $this->socket = $socket;
            $this->onInitialize($args);
        } catch (Throwable $e) {
            $this->socket = null;
            socket_close($socket);
            throw $e;
        }
    }
    
    public abstract function onInitialize(FarahUrlArguments $args): void;
    
    public function toChunks(): Generator {
        $socket = $this->socket ?? throw new LogicException('Initialize the daemon before accepting messages.');
        $this->isRunning = true;
        while ($this->isRunning) {
            $client = @socket_accept($socket);
            if ($client === false) {
                if (in_array(socket_last_error($socket), [SOCKET_EWOULDBLOCK, SOCKET_EINTR], true)) {
                    continue;
                }
                throw SocketIO::failure('accept daemon connection', $socket);
            }
            try {
                $data = SocketIO::read($client);
                if ($data !== '') {
                    try {
                        $data = json_decode($data, true);
                        foreach ($this->onMessage($data) as $type => $response) {
                            switch ($type) {
                                case static::STDOUT:
                                    SocketIO::write($client, json_encode($response, JSON_THROW_ON_ERROR));
                                    break;
                                case static::STDERR:
                                    yield $response;
                                    break;
                                default:
                                    throw new BadMethodCallException("Response type '$type' is not supported by this implementation.");
                            }
                        }
                    } catch (Throwable $e) {
                        yield $e->getMessage() . PHP_EOL;
                    }
                    SocketIO::write($client, "\0");
                }
            } catch (RuntimeException $e) {
                yield $e->getMessage() . PHP_EOL;
            } finally {
                socket_close($client);
            }
        }
    }
    
    /**
     * @return iterable<int, mixed>
     */
    public abstract function onMessage(mixed $message): iterable;
    
    public function stop(): void {
        $this->isRunning = false;
    }
    
    /**
     * @return iterable<int, string>
     */
    protected function log(string $message): iterable {
        yield static::STDERR => sprintf('[%s] %s%s', date(DateTimeFormatter::FORMAT_DATETIME), $message, PHP_EOL);
    }
    
    /**
     * @return iterable<int, string>
     */
    protected function respondWith(string $message): iterable {
        yield static::STDOUT => $message;
    }
}
