<?php

declare(strict_types = 1);
namespace Slothsoft\Farah\Module\FarahUrl;


/**
 *
 * @author Daniel Schulz
 *        
 */
class FarahUrlPath 
{
    public static function createEmpty(): FarahUrlArguments {
        return self::create('');
    }
    public static function createFromString(string $path) : FarahUrlPath {
        if (substr($path, 0, 1) !== '/') {
            $path = '/' . $path;
        }
        return self::create($path);
    }
    private static function create(string $id) : FarahUrlPath {
        static $cache = [];
        if (!isset($cache[$id])) {
            $cache[$id] = new FarahUrlPath($id);
        }
        return $cache[$id];
    }
    
    private $id;

    private function __construct(string $id)
    {
        $this->id = $id;
    }
    public function __toString() : string {
        return $this->id;
    }
}

