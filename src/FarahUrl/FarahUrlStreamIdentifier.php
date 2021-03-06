<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\FarahUrl;

use Ds\Hashable;

/**
 *
 * @author Daniel Schulz
 *        
 */
class FarahUrlStreamIdentifier implements Hashable {

    public static function createEmpty(): FarahUrlStreamIdentifier {
        return self::create('');
    }

    public static function createFromString(string $fragment): FarahUrlStreamIdentifier {
        return self::create($fragment);
    }

    private static function create(string $id): FarahUrlStreamIdentifier {
        static $cache = [];
        if (! isset($cache[$id])) {
            $cache[$id] = new FarahUrlStreamIdentifier($id);
        }
        return $cache[$id];
    }

    private $id;

    private function __construct(string $id) {
        $this->id = $id;
    }

    public function __toString(): string {
        return $this->id;
    }

    public function equals($obj): bool {
        return ($obj instanceof self and ((string) $this === (string) $obj));
    }

    public function hash() {
        return (string) $this;
    }
}

