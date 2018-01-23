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
    public function getItems(array $keys = array())
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
    public function deleteItem($key)
    {}

    /**
     * (non-PHPdoc)
     *
     * @see \Psr\Cache\CacheItemPoolInterface::hasItem()
     *
     */
    public function hasItem($key)
    {}

    /**
     * (non-PHPdoc)
     *
     * @see \Psr\Cache\CacheItemPoolInterface::clear()
     *
     */
    public function clear()
    {}

    /**
     * (non-PHPdoc)
     *
     * @see \Psr\Cache\CacheItemPoolInterface::save()
     *
     */
    public function save(CacheItemInterface $item)
    {}

    /**
     * (non-PHPdoc)
     *
     * @see \Psr\Cache\CacheItemPoolInterface::commit()
     *
     */
    public function commit()
    {}

    /**
     * (non-PHPdoc)
     *
     * @see \Psr\Cache\CacheItemPoolInterface::getItem()
     *
     */
    public function getItem($uri)
    {
        assert($uri instanceof AssetUri);
        $key = $uri->toString();
        if (!isset($this->itemList[$key])) {
            $this->itemList[$key] = new AssetCacheItem($this, $uri);
        }
        return $this->itemList[$key];
    }

    /**
     * (non-PHPdoc)
     *
     * @see \Psr\Cache\CacheItemPoolInterface::deleteItems()
     *
     */
    public function deleteItems(array $keys)
    {}

    /**
     * (non-PHPdoc)
     *
     * @see \Psr\Cache\CacheItemPoolInterface::saveDeferred()
     *
     */
    public function saveDeferred(CacheItemInterface $item)
    {}
}

