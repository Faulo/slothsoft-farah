<?php
declare(strict_types = 1);

namespace Slothsoft\Farah;

use Closure;

/**
 * Legacy wrapper around a callable with HTTP command execution options.
 *
 * @author Daniel Schulz
 * @since 2017-06-21
 * @deprecated Included for historical compatibility only. This API is deprecated and should not be used in new code.
 */
final class HTTPClosure {
    
    /** @var array<string, mixed> */
    protected array $options = [
        'isThreaded' => false,
        'isCachable' => false
    ];
    
    protected Closure $task;
    
    /**
     * @param array<string, mixed> $options
     */
    public function __construct(array $options, Closure $task) {
        $this->options = $options + $this->options;
        $this->task = $task;
    }
    
    public function run(mixed ...$args): mixed {
        return ($this->task)(...$args);
    }
    
    public function isThreaded(): bool {
        return (bool) $this->options['isThreaded'];
    }
    
    public function isCachable(): bool {
        return (bool) $this->options['isCachable'];
    }
}
