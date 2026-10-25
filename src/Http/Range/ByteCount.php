<?php
declare(strict_types = 1);

namespace Slothsoft\Farah\Http\Range;

use InvalidArgumentException;
use OverflowException;
use Stringable;
use UnderflowException;

/**
 * Exact, nonnegative byte arithmetic without platform integer limits.
 * @internal
 */
final readonly class ByteCount implements Stringable {
    private string $value;
    
    public function __construct(int|string $value) {
        $value = (string) $value;
        if (! preg_match('/\A[0-9]+\z/', $value)) {
            throw new InvalidArgumentException('A byte count must be a nonnegative decimal integer.');
        }
        $this->value = ltrim($value, '0') ?: '0';
    }
    
    public function __toString(): string {
        return $this->value;
    }
    
    public function compare(self $other): int {
        return (strlen($this->value) <=> strlen($other->value)) ?: (strcmp($this->value, $other->value) <=> 0);
    }
    
    public function isZero(): bool {
        return $this->value === '0';
    }
    
    public function toInt(): int {
        if ($this->compare(new self(PHP_INT_MAX)) > 0) {
            throw new OverflowException('The byte count exceeds PHP_INT_MAX.');
        }
        return (int) $this->value;
    }
    
    public function add(self $other): self {
        $result = '';
        $carry = 0;
        for ($i = strlen($this->value) - 1, $j = strlen($other->value) - 1; $i >= 0 || $j >= 0 || $carry; $i--, $j--) {
            $sum = ($i >= 0 ? (int) $this->value[$i] : 0) + ($j >= 0 ? (int) $other->value[$j] : 0) + $carry;
            $result .= ($sum % 10);
            $carry = intdiv($sum, 10);
        }
        return new self(strrev($result));
    }
    
    public function subtract(self $other): self {
        if ($this->compare($other) < 0) {
            throw new UnderflowException('A byte count cannot be negative.');
        }
        $result = '';
        $borrow = 0;
        for ($i = strlen($this->value) - 1, $j = strlen($other->value) - 1; $i >= 0; $i--, $j--) {
            $digit = (int) $this->value[$i] - ($j >= 0 ? (int) $other->value[$j] : 0) - $borrow;
            $borrow = $digit < 0 ? 1 : 0;
            $result .= ($digit + 10 * $borrow);
        }
        return new self(strrev($result));
    }
}
