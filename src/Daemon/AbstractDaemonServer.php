<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Daemon;

use Slothsoft\Core\Calendar\DateTimeFormatter;
use Slothsoft\Core\IO\Writable\ChunkWriterInterface;
use Slothsoft\Farah\FarahUrl\FarahUrlArguments;
use BadMethodCallException;
use Generator;
use Throwable;

abstract class AbstractDaemonServer implements ChunkWriterInterface {
    public const DAEMON_DOMAIN = AF_INET;
    public const DAEMON_TYPE = SOCK_STREAM;
    public const DAEMON_PROTOCOL = SOL_TCP;
    public const DAEMON_ADDRESS = '127.0.0.1';
    
    public const STDOUT = 1;
    public const STDERR = 2;
    
    private $port;
    private $socket;
    private $isRunning;
    public function __construct(int $port) {
        $this->port = $port;
    }
    
    public function init(FarahUrlArguments $args) {
        $this->socket = socket_create(static::DAEMON_DOMAIN, static::DAEMON_TYPE, static::DAEMON_PROTOCOL);
        socket_bind($this->socket, static::DAEMON_ADDRESS, $this->port);
        socket_listen($this->socket);
        socket_set_nonblock($this->socket);
        $this->onInitialize($args);
    }
    
    public abstract function onInitialize(FarahUrlArguments $args):void;
    
    public function toChunks() : Generator {
        $this->isRunning = true;
        while ($this->isRunning) {
            if (($client = socket_accept($this->socket)) !== false) {
                while (is_resource($client)) {
                    $data = (string) socket_read($client, 65535);
                    if ($data !== '') {
                        try {
                            $data = json_decode($data, true);
                            foreach ($this->onMessage($data) as $type => $response) {
                                switch ($type) {
                                    case static::STDOUT:
                                        socket_write($client, json_encode($response));
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
                        socket_write($client, "\0");
                        socket_close($client);
                        break;
                    }
                }
            }
        }
    }
    
    public abstract function onMessage($message):iterable;
    
    public function stop() {
        $this->isRunning = false;
    }
    
    protected function log(string $message) :iterable{
        yield static::STDERR => sprintf('[%s] %s%s', date(DateTimeFormatter::FORMAT_DATETIME), $message, PHP_EOL);
    }
    protected function respondWith(string $message) :iterable{
        yield static::STDOUT => $message;
    }
}