<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Daemon;

use Slothsoft\Farah\FarahUrl\FarahUrl;
use Slothsoft\Farah\Module\Module;

class DaemonClient {

    public static function fromUrl(FarahUrl $url): DaemonClient {
        $asset = Module::resolveToAsset($url);
        $manifest = $asset->getManifestElement();
        $port = (int) $manifest->getAttribute('port');
        return new DaemonClient($port);
    }

    private $port;

    public function __construct(int $port) {
        $this->port = $port;
    }

    public function call($message): iterable {
        $message = json_encode($message);
        $socket = socket_create(AbstractDaemonServer::DAEMON_DOMAIN, AbstractDaemonServer::DAEMON_TYPE, AbstractDaemonServer::DAEMON_PROTOCOL);
        socket_connect($socket, AbstractDaemonServer::DAEMON_ADDRESS, $this->port);
        socket_write($socket, $message);

        while (is_resource($socket)) {
            $data = (string) socket_read($socket, 65535);
            if ($data !== '') {
                if ($data === "\0") {
                    socket_close($socket);
                    break;
                } else {
                    yield json_decode($data, true);
                }
            }
        }
    }
}