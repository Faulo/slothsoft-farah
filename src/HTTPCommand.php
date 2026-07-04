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
    
    protected $exec;
    
    public function __construct($command) {
        $this->exec = new InterExec($command);
    }
    
    public function getMime(): ?string {
        return null;
    }
    
    public function getEncoding(): ?string {
        return null;
    }
    
    public function getHeaderList(): array {
        return [];
    }
    
    public function run(): void {
        $this->exec->run();
    }
    
    // EventTarget
    public function addEventListener($type, Closure $listener, $capture = false): void {
        $this->exec->on($type, function (InterExec $exec, $data) use ($type, $listener) {
            $eve = new HTTPEvent($type);
            $eve->target = $this;
            $eve->data = $data;
            $listener($eve);
        });
    }
    
    public function removeEventListener($type, $listener, $capture = false): void {
        // not implemented
    }
    
    public function dispatchEvent($event): void {
        // not implemented
    }
}
