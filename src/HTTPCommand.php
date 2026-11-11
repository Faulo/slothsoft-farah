<?php
declare(strict_types = 1);

namespace Slothsoft\Farah;

use Closure;
use Slothsoft\Core\InterExec;

/**
 * Legacy command dispatcher for event-driven HTTP work units.
 *
 * @author Daniel Schulz
 * @since 2017-12-28
 * @deprecated Included for historical compatibility only. This API is deprecated and should not be used in new code.
 */
final class HTTPCommand {
    
    protected InterExec $exec;
    
    public function __construct(string $command) {
        $this->exec = new InterExec($command);
    }
    
    public function getMime(): null {
        return null;
    }
    
    public function getEncoding(): null {
        return null;
    }
    
    /**
     * @return array<string, string|string[]>
     */
    public function getHeaderList(): array {
        return [];
    }
    
    public function run(): void {
        $this->exec->run();
    }
    
    // EventTarget
    
    public function addEventListener(string $type, Closure $listener, bool $capture = false): void {
        $this->exec->on($type, function (InterExec $exec, $data) use ($type, $listener) {
            $eve = new HTTPEvent($type);
            $eve->target = $this;
            $eve->data = $data;
            $listener($eve);
        });
    }
    
    public function removeEventListener(string $type, Closure $listener, bool $capture = false): void {
        // not implemented
    }
    
    public function dispatchEvent(HTTPEvent $event): void {
        // not implemented
    }
}
