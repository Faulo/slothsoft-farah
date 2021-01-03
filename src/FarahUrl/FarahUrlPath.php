<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\FarahUrl;

use Ds\Hashable;
use Slothsoft\Farah\Exception\MalformedUrlException;

/**
 *
 * @author Daniel Schulz
 *        
 */
class FarahUrlPath implements Hashable {

    const SEPARATOR = '/';

    public static function createEmpty(): self {
        return self::create('');
    }

    public static function createFromString(string $path, FarahUrlPath $base = null): self {
        if ($base and substr($path, 0, 1) !== self::SEPARATOR) {
            return self::create(self::normalize($base . self::SEPARATOR . $path));
        } else {
            return self::create(self::normalize($path));
        }
    }

    private static function create(string $id): self {
        static $cache = [];
        if (! isset($cache[$id])) {
            $cache[$id] = new self($id);
        }
        return $cache[$id];
    }

    private static function normalize(string $path): string {
        $segments = [];
        if ($path !== '') {
            foreach (explode(self::SEPARATOR, str_replace('\\', self::SEPARATOR, $path)) as $val) {
                switch ($val) {
                    case '':
                        break;
                    case '.':
                        break;
                    case '..':
                        if (! count($segments)) {
                            throw new MalformedUrlException($path);
                        }
                        array_pop($segments);
                        break;
                    default:
                        $segments[] = $val;
                        break;
                }
            }
        }
        return self::SEPARATOR . implode(self::SEPARATOR, $segments);
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

