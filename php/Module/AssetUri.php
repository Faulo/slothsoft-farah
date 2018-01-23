<?php
namespace Slothsoft\Farah\Module;

use DomainException;
use InvalidArgumentException;

/**
 *
 * @author Daniel Schulz
 *        
 */
class AssetUri
{
    public static function createFromUri(string $uri) : AssetUri {
        $res = parse_url($uri);
        if ($res === false) {
            throw new InvalidArgumentException("invalid uri: $uri");
        }
        if (!isset($res['scheme'], $res['user'], $res['host'], $res['path'])) {
            throw new InvalidArgumentException("incomplete uri: $uri");
        }
        if ($res['scheme'] !== 'farah') {
            throw new DomainException("not a farah uri: $uri");
        }
        return new AssetUri(
            $uri,
            $res['user'],
            $res['host'],
            $res['path'],
            $res['query'] ?? ''
        );
    }
    public static function createFromReference(string $ref, Module $context, array $args = []) : AssetUri {
        $res = parse_url($ref);
        if ($res === false) {
            throw new InvalidArgumentException("invalid reference: $ref");
        }
        if (!isset($res['user'])) {
            $res['user'] = $context->getVendor();
        }
        if (!isset($res['host'])) {
            $res['host'] = $context->getName();
        }
        if (isset($res['path'])) {
            if ($res['path'][0] !== '/') {
                $res['path'] = '/' . $res['path'];
            }
        } else {
            $res['path'] = '/';
        }
        if (isset($res['query'])) {
            parse_str($res['query'], $tmp);
            $args += $tmp;
        }
        
        if (count($args)) {
            $res['query'] = http_build_query($args);
            $uri = "farah://$res[user]@$res[host]$res[path]?$res[query]";
        } else {
            $res['query'] = '';
            $uri = "farah://$res[user]@$res[host]$res[path]";
        }
        
        return new AssetUri(
            $uri,
            $res['user'],
            $res['host'],
            $res['path'],
            $res['query']
        );
    }
    private $uri;
    private $vendor;
    private $module;
    private $path;
    private $query;
    
    public function __construct(string $uri, string $vendor, string $module, string $path, string $query) {
        $this->uri = $uri;
        $this->vendor = $vendor;
        $this->module = $module;
        $this->path = $path;
        $this->query = $query;
    }
    public function __toString() : string
    {
        return $this->uri;
    }
    public function toString() : string
    {
        return $this->uri;
    }
    public function getVendor() : string
    {
        return $this->vendor;
    }
    public function getModule() : string
    {
        return $this->module;
    }
    public function getPath() : string
    {
        return $this->path;
    }
    public function getQuery() : string
    {
        return $this->query;
    }
    public function getQueryArray() : array {
        if (strlen($this->query)) {
            parse_str($this->query, $ret);
            return $ret;
        } else {
            return [];
        }
    }
}

