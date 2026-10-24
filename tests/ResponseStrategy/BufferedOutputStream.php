<?php
declare(strict_types = 1);

namespace Slothsoft\Farah\ResponseStrategy;

/** Test destination that makes written content visible only on flush. */
final class BufferedOutputStream {
    
    public mixed $context;
    
    public static string $published = '';
    
    private string $pending = '';
    
    /**
     * Parameters are required by PHP's stream-wrapper callback contract.
     * @noinspection PhpUnusedParameterInspection
     */
    public function stream_open(string $path, string $mode, int $options, ?string &$openedPath): bool {
        self::$published = '';
        return true;
    }
    
    public function stream_write(string $data): int {
        $this->pending .= $data;
        return strlen($data);
    }
    
    public function stream_flush(): bool {
        self::$published .= $this->pending;
        $this->pending = '';
        return true;
    }
}
