<?php
declare(strict_types = 1);

namespace Slothsoft\Farah\Http\Range;

/**
 * Parses byte-range sets independently of the representation size.
 * @internal
 */
final readonly class RangeRequest {
    /** @param list<array{?ByteCount, ?ByteCount}> $ranges */
    private function __construct(private array $ranges) {
    }
    
    /** Returns null for unsupported units or malformed syntax. */
    public static function parse(string $header): ?self {
        if (! preg_match('/\Abytes=(.*)\z/i', trim($header, " \t"), $match)) {
            return null;
        }
        $ranges = [];
        foreach (explode(',', $match[1]) as $member) {
            $member = trim($member, " \t");
            // HTTP list syntax permits empty elements around valid members.
            if ($member === '') {
                continue;
            }
            if (! preg_match('/\A([0-9]*)-([0-9]*)\z/', $member, $parts) || ($parts[1] === '' && $parts[2] === '')) {
                return null;
            }
            $start = $parts[1] === '' ? null : new ByteCount($parts[1]);
            $end = $parts[2] === '' ? null : new ByteCount($parts[2]);
            if ($start !== null && $end !== null && $start->compare($end) > 0) {
                return null;
            }
            $ranges[] = [$start, $end];
        }
        return $ranges === [] ? null : new self($ranges);
    }
    
    public function getRangeCount(): int {
        return count($this->ranges);
    }
    
    /** @return list<ByteRange> Satisfiable ranges in request order. */
    public function resolve(ByteCount $size): array {
        if ($size->isZero()) {
            return [];
        }
        $last = $size->subtract(new ByteCount(1));
        $resolved = [];
        foreach ($this->ranges as [$start, $end]) {
            if ($start === null) {
                if ($end === null || $end->isZero()) {
                    continue;
                }
                $start = $end->compare($size) >= 0 ? new ByteCount(0) : $size->subtract($end);
                $end = $last;
            } else {
                if ($start->compare($size) >= 0) {
                    continue;
                }
                if ($end === null || $end->compare($last) > 0) {
                    $end = $last;
                }
            }
            $resolved[] = new ByteRange($start, $end, $size);
        }
        return $resolved;
    }
}
