<?php
declare(strict_types = 1);

namespace Slothsoft\Farah\Http\Range;

use InvalidArgumentException;

/** @internal */
final readonly class ByteRange {
    public function __construct(public ByteCount $start, public ByteCount $end, public ByteCount $size) {
        if ($start->compare($end) > 0 || $end->compare($size) >= 0) {
            throw new InvalidArgumentException('A resolved range must lie inside the representation.');
        }
    }
    
    public function getLength(): ByteCount {
        return $this->end->subtract($this->start)->add(new ByteCount(1));
    }
    
    public function getContentRange(): string {
        return "bytes $this->start-$this->end/$this->size";
    }
}
