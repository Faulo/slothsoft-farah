<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\FarahUrl;

/**
 *
 * @author Daniel Schulz
 *        
 */
class FarahUrlStreamIdentifier
{

    public static function createEmpty(): FarahUrlStreamIdentifier
    {
        return self::create('');
    }

    public static function createFromString(string $path): FarahUrlStreamIdentifier
    {
        return self::create($path);
    }

    private static function create(string $id): FarahUrlStreamIdentifier
    {
        static $cache = [];
        if (! isset($cache[$id])) {
            $cache[$id] = new FarahUrlStreamIdentifier($id);
        }
        return $cache[$id];
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

