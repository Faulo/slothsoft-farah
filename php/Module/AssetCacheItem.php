<?php
namespace Slothsoft\Farah\Module;

use Psr\Cache\CacheItemInterface;

/**
 *
 * @author Daniel Schulz
 *        
 */
class AssetCacheItem implements CacheItemInterface
{
    private $ownerPool;
    private $uri;
    private $value;

    /**
     */
    public function __construct(AssetCache $ownerPool, AssetUri $uri)
    {
        $this->ownerPool = $ownerPool;
        $this->uri = $uri;
    }

    /**
     * (non-PHPdoc)
     *
     * @see \Psr\Cache\CacheItemInterface::getKey()
     *
     */
    public function getKey()
    {
        return $this->uri->toString();
    }

    /**
     * (non-PHPdoc)
     *
     * @see \Psr\Cache\CacheItemInterface::expiresAfter()
     *
     */
    public function expiresAfter($time)
    {}

    /**
     * (non-PHPdoc)
     *
     * @see \Psr\Cache\CacheItemInterface::set()
     *
     */
    public function set($value)
    {
        $this->value = $value;
    }

    /**
     * (non-PHPdoc)
     *
     * @see \Psr\Cache\CacheItemInterface::isHit()
     *
     */
    public function isHit()
    {
        return (bool) $this->value;
    }

    /**
     * (non-PHPdoc)
     *
     * @see \Psr\Cache\CacheItemInterface::get()
     *
     */
    public function get()
    {
        return $this->value;
    }

    /**
     * (non-PHPdoc)
     *
     * @see \Psr\Cache\CacheItemInterface::expiresAt()
     *
     */
    public function expiresAt($expiration)
    {}
}

