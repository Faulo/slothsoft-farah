<?php
namespace Slothsoft\Farah\Module;

use DomainException;
use InvalidArgumentException;

/**
 *
 * @author Daniel Schulz
 *        
 */
class FarahUrl
{

    public static function createFromUri(string $uri, string $base = '', array $args = []): FarahUrl
    {
        $res = parse_url($uri);
        if ($res === false) {
            throw new InvalidArgumentException("invalid uri: $uri");
        }
        if ($base !== '') {
            $tmp = parse_url($base);
            if ($tmp === false) {
                throw new InvalidArgumentException("invalid base uri: $base");
            }
            $res += $tmp; // TODO: only apply inheritable variables
        }
        if (! isset($res['scheme'], $res['user'], $res['host'])) {
            throw new InvalidArgumentException("incomplete uri: $uri");
        }
        if ($res['scheme'] !== 'farah') {
            throw new DomainException("not a farah url: $uri");
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
        }
        return new FarahUrl($res['user'], $res['host'], $res['path'], $res['query'] ?? '', $res['fragment'] ?? '');
    }

    public static function createFromReference(string $ref, Module $context, array $args = []): FarahUrl
    {
        return self::createFromUri($ref, $context->getId(), $args);
    }

    private $href;

    private $vendor;

    private $module;

    private $path;

    private $query;

    private $fragment;

    public function __construct(string $vendor, string $module, string $path, string $query, string $fragment)
    {
        $this->vendor = $vendor;
        $this->module = $module;
        $this->path = $path;
        $this->query = $query;
        $this->fragment = $fragment;
        
        $this->href = "farah://$this->vendor@$this->module$this->path";
        if (strlen($this->query)) {
            $this->href .= "?$this->query";
        }
        if (strlen($this->fragment)) {
            $this->href .= "#$this->query";
        }
    }

    public function __toString(): string
    {
        return $this->href;
    }

    public function toString(): string
    {
        return $this->href;
    }

    public function getVendor(): string
    {
        return $this->vendor;
    }

    public function getModule(): string
    {
        return $this->module;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getQuery(): string
    {
        return $this->query;
    }

    public function getQueryArray(): array
    {
        if (strlen($this->query)) {
            parse_str($this->query, $ret);
            return $ret;
        } else {
            return [];
        }
    }

    public function getFragment(): string
    {
        return $this->fragment;
    }

    public function withQueryArray(array $query): FarahUrl
    {
        return new FarahUrl($this->vendor, $this->module, $this->path, http_build_query($query), $this->fragment);
    }
}

