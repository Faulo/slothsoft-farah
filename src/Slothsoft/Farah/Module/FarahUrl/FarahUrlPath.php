<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\FarahUrl;

use Slothsoft\Farah\Exception\MalformedUrlException;

/**
 *
 * @author Daniel Schulz
 *        
 */
class FarahUrlPath
{
    const SEPARATOR = '/';

    public static function createEmpty(): FarahUrlArguments
    {
        return self::create('');
    }

    public static function createFromString(string $path, FarahUrlPath $base = null): FarahUrlPath
    {
        if ($base and substr($path, 0, 1) !== self::SEPARATOR) {
            return self::create(self::normalize($base.self::SEPARATOR.$path));
        } else {
            return self::create(self::normalize($path));
        }
    }

    private static function create(string $id): FarahUrlPath
    {
        static $cache = [];
        if (! isset($cache[$id])) {
            $cache[$id] = new FarahUrlPath($id);
        }
        return $cache[$id];
    }
    private static function normalize(string $path): string {
        $segments = [];
        if ($path !== '') {
            foreach (explode(self::SEPARATOR, $path) as $i => $val) {
                switch ($val) {
                    case '':
                        break;
                    case '.':
                        break;
                    case '..':
                        if (!count($segments)) {
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
        return count($segments)
        ? self::SEPARATOR . implode(self::SEPARATOR, $segments)
        : self::SEPARATOR;
    }

    private $id;

    private function __construct(string $id)
    {
        $this->id = $id;
    }

    public function __toString(): string
    {
        return $this->id;
    }
}

