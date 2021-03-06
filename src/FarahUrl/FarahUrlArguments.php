<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\FarahUrl;

use Ds\Hashable;
use ArrayIterator;
use IteratorAggregate;

/**
 *
 * @author Daniel Schulz
 *        
 */
class FarahUrlArguments implements IteratorAggregate, Hashable {

    public static function createEmpty(): FarahUrlArguments {
        return self::create('', []);
    }

    public static function createFromMany(FarahUrlArguments ...$argsList): FarahUrlArguments {
        $data = [];
        foreach ($argsList as $args) {
            $data += $args->getValueList();
        }
        ksort($data);
        return self::createFromValueList($data);
    }

    public static function createFromQuery(string $query): FarahUrlArguments {
        if ($query === '') {
            return self::createEmpty();
        }
        $valueList = [];
        parse_str($query, $valueList);
        return self::create($query, $valueList);
    }

    public static function createFromValueList(array $valueList): FarahUrlArguments {
        return self::create(http_build_query($valueList), $valueList);
    }

    private static function create(string $id, array $valueList): FarahUrlArguments {
        static $cache = [];
        if (! isset($cache[$id])) {
            $cache[$id] = new FarahUrlArguments($id, $valueList);
        }
        return $cache[$id];
    }

    private $id;

    private $data;

    private function __construct(string $id, array $data) {
        $this->id = $id;
        $this->data = $data;
    }

    public function __toString(): string {
        return $this->id;
    }

    public function get(string $key, $default = null) {
        return $this->data[$key] ?? $default;
    }

    public function set(string $key, $val) {
        $this->data[$key] = $val;
    }

    public function has(string $key) {
        return isset($this->data[$key]);
    }

    public function delete(string $key) {
        unset($this->data[$key]);
    }

    public function getValueList(): array {
        return $this->data;
    }

    public function getNameList(): array {
        return array_keys($this->data);
    }

    public function getIterator(): iterable {
        return new ArrayIterator($this->data);
    }

    public function isEmpty(): bool {
        return $this->id === '';
    }

    public function withArgument(string $key, $val): FarahUrlArguments {
        if (isset($this->data[$key]) and $this->data[$key] === $val) {
            return $this;
        } else {
            return self::createFromValueList([
                $key => $val
            ] + $this->data);
        }
    }

    public function withArguments(FarahUrlArguments $args): FarahUrlArguments {
        if ($this->isEmpty()) {
            return $args;
        }
        if ($args->isEmpty()) {
            return $this;
        }
        return self::createFromMany($args, $this);
    }

    public function equals($obj): bool {
        return ($obj instanceof self and ((string) $this === (string) $obj));
    }

    public function hash() {
        return (string) $this;
    }
}

