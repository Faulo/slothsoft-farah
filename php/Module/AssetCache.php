<?php
namespace Slothsoft\Farah\Module;

use Psr\Cache\CacheItemPoolInterface;
use Psr\Cache\CacheItemInterface;

/**
 *
 * @author Daniel Schulz
 *        
 */
class AssetCache implements CacheItemPoolInterface
{

    private $itemList;

    /**
     */
    public function __construct()
    {}

    /**
     * (non-PHPdoc)
     *
     * @see \Psr\Cache\CacheItemPoolInterface::getItems()
     *
     */
    public function getItems(array $keys = array()): array
    {
        $ret = [];
        foreach ($keys as $key) {
            $ret[] = $this->getItem($key);
        }
        return $ret;
    }

    /**
     * (non-PHPdoc)
     *
     * @see \Psr\Cache\CacheItemPoolInterface::deleteItem()
     *
     */
    public function deleteItem($key): bool
    {}

    /**
     * (non-PHPdoc)
     *
     * @see \Psr\Cache\CacheItemPoolInterface::hasItem()
     *
     */
    public function hasItem($key): bool
    {}

    /**
     * (non-PHPdoc)
     *
     * @see \Psr\Cache\CacheItemPoolInterface::clear()
     *
     */
    public function clear(): bool
    {}

    /**
     * (non-PHPdoc)
     *
     * @see \Psr\Cache\CacheItemPoolInterface::save()
     *
     */
    public function save(CacheItemInterface $item): bool
    {
        return true;
    }

    /**
     * (non-PHPdoc)
     *
     * @see \Psr\Cache\CacheItemPoolInterface::commit()
     *
     */
    public function commit(): bool
    {}

    /**
     * (non-PHPdoc)
     *
     * @see \Psr\Cache\CacheItemPoolInterface::getItem()
     *
     */
    public function getItem($url): CacheItemInterface
    {
        assert($url instanceof FarahUrl);
        
        $key = $url->toString();
        if (! isset($this->itemList[$key])) {
            $this->itemList[$key] = new AssetCacheItem($this, $url);
        }
        return $this->itemList[$key];
    }

    /**
     * (non-PHPdoc)
     *
     * @see \Psr\Cache\CacheItemPoolInterface::deleteItems()
     *
     */
    public function deleteItems(array $keys): bool
    {}

    /**
     * (non-PHPdoc)
     *
     * @see \Psr\Cache\CacheItemPoolInterface::saveDeferred()
     *
     */
    public function saveDeferred(CacheItemInterface $item): bool
    {}
}

