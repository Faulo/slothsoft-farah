<?php
declare(strict_types = 1);

namespace Slothsoft\Farah\Http\Range;

use InvalidArgumentException;

/** @internal */
final readonly class ByteRange {
    public function __construct(public int $start, public int $end, public int $size) {
        if ($start < 0 || $start > $end || $end >= $size) {
            throw new InvalidArgumentException('A resolved range must lie inside the representation.');
        }
    }
    
    public function getLength(): int {
        return $this->end - $this->start + 1;
    }
    
    public function getContentRange(): string {
        return "bytes $this->start-$this->end/$this->size";
    }
}
