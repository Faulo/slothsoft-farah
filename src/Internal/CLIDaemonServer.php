<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Internal;

use Slothsoft\Farah\Daemon\AbstractDaemonServer;
use Slothsoft\Farah\FarahUrl\FarahUrlArguments;
use Symfony\Component\Process\Process;

/**
 *
 * @author Daniel Schulz
 *
 */
class CLIDaemonServer extends AbstractDaemonServer {
    public function onInitialize(FarahUrlArguments $args): void {
        
    }
    public function onMessage($message): iterable {
        assert(is_array($message));
        $process = new Process($message);
        yield from $this->log($process->getCommandLine());
        $process->setTimeout(0);
        $process->start();
        foreach ($process as $type => $data) {
            if ($type === $process::OUT) {
                yield from $this->respondWith($data);
            } else {
                yield from $this->log($data);
            }
        }
    }    
}