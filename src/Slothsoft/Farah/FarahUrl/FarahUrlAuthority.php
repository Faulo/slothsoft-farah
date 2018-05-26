<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\FarahUrl;

use Ds\Hashable;
use Slothsoft\Farah\Exception\IncompleteUrlException;

/**
 *
 * @author Daniel Schulz
 *        
 */
class FarahUrlAuthority implements Hashable
{

    public static function createFromVendorAndModule(string $vendor, string $module): FarahUrlAuthority
    {
        $id = "farah://$vendor@$module";
        if ($vendor === '' or $module === '') {
            throw new IncompleteUrlException($id, 'vendor or module');
        }
        return self::create($id, $vendor, $module);
    }
    
    public static function createFromHttpAuthority(string $authority): FarahUrlAuthority
    {
        list($vendor, $module) = explode('@', $authority);
        return self::createFromVendorAndModule($vendor, $module);
    }

    private static function create(string $id, string $vendor, string $module): FarahUrlAuthority
    {
        static $cache = [];
        if (! isset($cache[$id])) {
            $cache[$id] = new FarahUrlAuthority($id, $vendor, $module);
        }
        return $cache[$id];
    }

    private $id;

    private $vendor;

    private $module;

    private function __construct(string $id, string $vendor, string $module)
    {
        $this->id = $id;
        $this->vendor = $vendor;
        $this->module = $module;
    }

    public function __toString(): string
    {
        return $this->id;
    }

    public function getProtocol(): string
    {
        return 'farah';
    }

    public function getVendor(): string
    {
        return $this->vendor;
    }

    public function getModule(): string
    {
        return $this->module;
    }

    public function equals($obj): bool
    {
        return ($obj instanceof self and ((string) $this === (string) $obj));
    }

    public function hash()
    {
        return (string) $this;
    }
}

