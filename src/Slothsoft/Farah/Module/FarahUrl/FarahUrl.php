<?php

declare(strict_types = 1);
namespace Slothsoft\Farah\Module\FarahUrl;

use DomainException;
use InvalidArgumentException;

/**
 *
 * @author Daniel Schulz
 *        
 */
class FarahUrl //TODO: implements Psr\Url
{
    public static function createFromComponents(FarahUrlAuthority $authority, FarahUrlPath $path, FarahUrlArguments $args): FarahUrl
    {
        $authorityId = (string) $authority;
        $pathId = (string) $path;
        $argsId = (string) $args;
        $id = $argsId === ''
            ? "$authorityId$pathId"
            : "$authorityId$pathId?$argsId";
        return self::create($id, $authority, $path, $args);
    }

    public static function createFromReference(
        string $ref,
        FarahUrlAuthority $contextAuthority = null,
        FarahUrlPath $contextPath = null,
        FarahUrlArguments $contextArguments = null): FarahUrl
    {
        $res = parse_url($ref);
        if ($res === false) {
            throw new InvalidArgumentException("Invalid url: $uri");
        }
        if ($contextAuthority) {
            if (! isset($res['scheme'])) {
                $res['scheme'] = $contextAuthority->getProtocol();
            }
            if (! isset($res['user'])) {
                $res['user'] = $contextAuthority->getVendor();
            }
            if (! isset($res['host'])) {
                $res['host'] = $contextAuthority->getModule();
            }
        }
        if (! isset($res['scheme'], $res['user'], $res['host'])) {
            throw new InvalidArgumentException("Incomplete url, missing authority information: $uri");
        }
        if ($res['scheme'] !== 'farah') {
            throw new DomainException("Not a farah url: $uri");
        }
        if ($contextPath) {
            if (! isset($res['path'])) {
                $res['path'] = $contextPath->toString();
            }
        }
        
        $authority = FarahUrlAuthority::createFromVendorAndModule($res['user'], $res['host']);
        $path = FarahUrlPath::createFromString($res['path'] ?? '');
        $arguments = FarahUrlArguments::createFromQuery($res['query'] ?? '');
        if ($contextArguments) {
            $arguments = FarahUrlArguments::createFromMany($arguments, $contextArguments);
        }
        
        return self::createFromComponents($authority, $path, $arguments);
    }
    private static function create(string $id, FarahUrlAuthority $authority, FarahUrlPath $path, FarahUrlArguments $args): FarahUrl
    {
        static $cache = [];
        if (!isset($cache[$id])) {
            $cache[$id] = new FarahUrl($id, $authority, $path, $args);
        }
        return $cache[$id];
    }

    private $id;

    private $authority;

    private $path;

    private $args;

    private function __construct(string $id, FarahUrlAuthority $authority, FarahUrlPath $path, FarahUrlArguments $args)
    {
        $this->id = $id;
        $this->authority = $authority;
        $this->path = $path;
        $this->args = $args;
    }
    public function __toString(): string
    {
        return $this->id;
    }
    
    
    public function getAuthority(): FarahUrlAuthority
    {
        return $this->authority;
    }
    public function getPath(): FarahUrlPath
    {
        return $this->path;
    }
    public function getArguments(): FarahUrlArguments
    {
        return $this->args;
    }
    
    
    public function withQueryArguments(FarahUrlArguments $args): FarahUrl
    {
        return self::createFromComponents($this->getAuthority(), $this->getPath(), $args);
    }
}

