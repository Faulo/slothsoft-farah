<?php
declare(strict_types = 1);

namespace Slothsoft\Farah\Http\Range;

/**
 * Parses byte-range sets independently of the representation size.
 * @internal
 */
final readonly class RangeRequest {
    /** @param list<array{?string, ?string}> $ranges */
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
            $start = $parts[1] === '' ? null : (ltrim($parts[1], '0') ?: '0');
            $end = $parts[2] === '' ? null : (ltrim($parts[2], '0') ?: '0');
            if ($start !== null && $end !== null && self::compareDecimals($start, $end) > 0) {
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
    public function resolve(int $size): array {
        if ($size <= 0) {
            return [];
        }
        $last = $size - 1;
        $sizeDigits = (string) $size;
        $resolved = [];
        foreach ($this->ranges as [$start, $end]) {
            if ($start === null) {
                if ($end === null || $end === '0') {
                    continue;
                }
                $firstByte = self::compareDecimals($end, $sizeDigits) >= 0 ? 0 : $size - (int) $end;
                $lastByte = $last;
            } else {
                if (self::compareDecimals($start, $sizeDigits) >= 0) {
                    continue;
                }
                $firstByte = (int) $start;
                $lastByte = $end === null || self::compareDecimals($end, $sizeDigits) >= 0 ? $last : (int) $end;
            }
            $resolved[] = new ByteRange($firstByte, $lastByte, $size);
        }
        return $resolved;
    }
    
    private static function compareDecimals(string $left, string $right): int {
        // Header values can exceed PHP_INT_MAX. Compare their digits before casting.
        return (strlen($left) <=> strlen($right)) ?: strcmp($left, $right);
    }
}
