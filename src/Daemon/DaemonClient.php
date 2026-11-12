<?php
declare(strict_types = 1);

namespace Slothsoft\Farah\Daemon;

use Slothsoft\Farah\FarahUrl\FarahUrl;
use Slothsoft\Farah\Module\Module;

/**
 * Client for invoking Farah daemon assets and reading their streamed responses.
 *
 * @author Daniel Schulz
 * @since 2021-01-01
 * @deprecated Included for historical compatibility only. This API is deprecated and should not be used in new code.
 */
final class DaemonClient {
    
    public static function fromUrl(FarahUrl $url): DaemonClient {
        $asset = Module::resolveToAsset($url);
        $manifest = $asset->getManifestElement();
        $port = (int) $manifest->getAttribute('port');
        return new DaemonClient($port);
    }
    
    private int $port;
    
    public function __construct(int $port) {
        $this->port = $port;
    }
    
    /**
     * @return iterable<mixed>
     */
    public function call(mixed $message): iterable {
        $message = json_encode($message, JSON_THROW_ON_ERROR);
        $socket = SocketIO::create(AbstractDaemonServer::DAEMON_DOMAIN, AbstractDaemonServer::DAEMON_TYPE, AbstractDaemonServer::DAEMON_PROTOCOL);
        try {
            if (! @socket_connect($socket, AbstractDaemonServer::DAEMON_ADDRESS, $this->port)) {
                throw SocketIO::failure(sprintf('connect to daemon at %s:%d', AbstractDaemonServer::DAEMON_ADDRESS, $this->port), $socket);
            }
            SocketIO::write($socket, $message);
            while (($data = SocketIO::read($socket)) !== '') {
                if ($data === "\0") {
                    break;
                }
                yield json_decode($data, true);
            }
        } finally {
            socket_close($socket);
        }
    }
}
