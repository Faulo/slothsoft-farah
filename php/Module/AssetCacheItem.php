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

    private $url;

    private $value;

    /**
     */
    public function __construct(AssetCache $ownerPool, FarahUrl $url)
    {
        $this->ownerPool = $ownerPool;
        $this->url = $url;
    }

    /**
     * (non-PHPdoc)
     *
     * @see \Psr\Cache\CacheItemInterface::getKey()
     *
     */
    public function getKey()
    {
        return $this->url->toString();
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
