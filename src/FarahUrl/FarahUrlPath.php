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
final class FarahUrlPath implements Hashable {
    
    public const SEPARATOR = '/';
    
    public static function createEmpty(): self {
        return self::create([]);
    }
    
    public static function createFromSegments(array $segments): self {
        return self::create($segments);
    }
    
    public static function createFromString(string $path, FarahUrlPath $base = null): self {
        if ($base and substr($path, 0, 1) !== self::SEPARATOR) {
            return self::create(self::normalize($base . self::SEPARATOR . $path));
        } else {
            return self::create(self::normalize($path));
        }
    }
    
    private static function create(array $segments): self {
        static $cache = [];
        $id = self::SEPARATOR . implode(self::SEPARATOR, $segments);
        if (! isset($cache[$id])) {
            $cache[$id] = new self($id, $segments);
        }
        return $cache[$id];
    }
    
    private static function normalize(string $path): array {
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
        return $segments;
    }
    
    private string $id;
    
    private array $segments;
    
    private function __construct(string $id, array $segments) {
        $this->id = $id;
        $this->segments = $segments;
    }
    
    public function __toString(): string {
        return $this->id;
    }
    
    public function getName(): string {
        assert(! $this->isEmpty(), 'Path is empty');
        
        return $this->segments[count($this->segments) - 1];
    }
    
    public function getSegments(): array {
        return $this->segments;
    }
    
    public function withoutLastSegment(): FarahUrlPath {
        assert(! $this->isEmpty(), 'Path is empty');
        
        return self::createFromSegments(array_slice($this->segments, 0, count($this->segments) - 1));
    }
    
    public function withLastSegment(string $name): FarahUrlPath {
        $segments = $this->segments;
        $segments[] = $name;
        return self::createFromSegments($segments);
    }
    
    public function isEmpty(): bool {
        return $this->id === self::SEPARATOR;
    }
    
    public function equals($obj): bool {
        return ($obj instanceof self and ((string) $this === (string) $obj));
    }
    
    public function hash() {
        return (string) $this;
    }
}

