<?php
declare(strict_types = 1);

namespace Slothsoft\Farah\Daemon;

use RuntimeException;
use Socket;

/** @internal */
final class SocketIO {
    
    public static function create(int $domain, int $type, int $protocol): Socket {
        $socket = @socket_create($domain, $type, $protocol);
        if ($socket === false) {
            throw self::failure('create socket');
        }
        return $socket;
    }
    
    public static function failure(string $operation, ?Socket $socket = null): RuntimeException {
        $error = socket_last_error($socket);
        return new RuntimeException(sprintf('Failed to %s: [%d] %s', $operation, $error, socket_strerror($error)));
    }
    
    public static function read(Socket $socket, int $length = 65535): string {
        $data = @socket_read($socket, $length);
        if ($data === false) {
            throw self::failure('read daemon socket', $socket);
        }
        return $data;
    }
    
    public static function write(Socket $socket, string $data): void {
        $length = strlen($data);
        $offset = 0;
        while ($offset < $length) {
            $written = @socket_write($socket, substr($data, $offset));
            if ($written === false || $written === 0) {
                throw self::failure('write daemon socket', $socket);
            }
            $offset += $written;
        }
    }
}
