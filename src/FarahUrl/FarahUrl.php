<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\FarahUrl;

use Ds\Hashable;
use Psr\Http\Message\UriInterface;
use Slothsoft\Farah\Exception\IncompleteUrlException;
use Slothsoft\Farah\Exception\MalformedUrlException;
use Slothsoft\Farah\Exception\ProtocolNotSupportedException;

/**
 *
 * @author Daniel Schulz
 *        
 */
class FarahUrl implements UriInterface, Hashable
{

    const SCHEME_DEFAULT = 'farah';

    /**
     *
     * @param FarahUrlAuthority|string $authority
     * @param FarahUrlPath|string $path
     * @param FarahUrlArguments|string $args
     * @param FarahUrlStreamIdentifier|string $fragment
     * @return FarahUrl
     */
    public static function createFromComponents(FarahUrlAuthority $authority, $path = null, $args = null, $fragment = null): FarahUrl
    {
        if ($path === null) {
            $path = FarahUrlPath::createEmpty();
        } elseif (is_string($path)) {
            $path = FarahUrlPath::createFromString($path);
        }
        
        if ($args === null) {
            $args = FarahUrlArguments::createEmpty();
        } elseif (is_string($args)) {
            $args = FarahUrlArguments::createFromQuery($args);
        }
        
        if ($fragment === null) {
            $fragment = FarahUrlStreamIdentifier::createEmpty();
        } elseif (is_string($fragment)) {
            $fragment = FarahUrlStreamIdentifier::createFromString($fragment);
        }
        
        $authorityId = (string) $authority;
        $pathId = (string) $path;
        $argsId = (string) $args;
        $fragmentId = (string) $fragment;
        
        $id = "$authorityId$pathId";
        if ($argsId !== '') {
            $id .= "?$argsId";
        }
        if ($fragmentId !== '') {
            $id .= "#$fragmentId";
        }
        
        return self::create($id, $authority, $path, $args, $fragment);
    }

    public static function createFromReference(string $ref, ?FarahUrl $contextUrl = null): FarahUrl
    {
        $res = parse_url($ref);
        if ($res === false) {
            throw new MalformedUrlException($ref);
        }
        if ($contextUrl) {
            $contextAuthority = $contextUrl->getAssetAuthority();
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
        if (! isset($res['scheme'], $res['user'], $res['host']) or $res['scheme'] === '' or $res['user'] === '' or $res['host'] === '') {
            throw new IncompleteUrlException($ref, 'scheme, user, or host');
        }
        if ($res['scheme'] !== self::SCHEME_DEFAULT) {
            throw new ProtocolNotSupportedException($res['scheme']);
        }
        
        $authority = FarahUrlAuthority::createFromVendorAndModule($res['user'], $res['host']);
        
        $path = FarahUrlPath::createFromString($res['path'] ?? FarahUrlPath::SEPARATOR, $contextUrl ? $contextUrl->getAssetPath() : null);
        
        $arguments = FarahUrlArguments::createFromQuery($res['query'] ?? '');
        if ($contextUrl) {
            $contextArguments = $contextUrl->getArguments();
            if ($contextArguments !== $arguments and ! $contextArguments->isEmpty()) {
                $arguments = FarahUrlArguments::createFromMany($arguments, $contextArguments);
            }
        }
        
        $fragment = FarahUrlStreamIdentifier::createFromString($res['fragment'] ?? '');
        
        return self::createFromComponents($authority, $path, $arguments, $fragment);
    }

    public static function createFromUri(UriInterface $uri): FarahUrl
    {
        return $uri instanceof FarahUrl ? $uri : self::createFromComponents(FarahUrlAuthority::createFromVendorAndModule($uri->getUserInfo(), $uri->getHost()), FarahUrlPath::createFromString($uri->getPath()), FarahUrlArguments::createFromQuery($uri->getQuery()), FarahUrlStreamIdentifier::createFromString($uri->getFragment()));
    }

    private static function create(string $id, FarahUrlAuthority $authority, FarahUrlPath $path, FarahUrlArguments $args, FarahUrlStreamIdentifier $fragment): FarahUrl
    {
        static $cache = [];
        if (! isset($cache[$id])) {
            $cache[$id] = new FarahUrl($id, $authority, $path, $args, $fragment);
        }
        return $cache[$id];
    }

    private $id;

    private $authority;

    private $path;

    private $args;

    private $fragment;

    private function __construct(string $id, FarahUrlAuthority $authority, FarahUrlPath $path, FarahUrlArguments $args, FarahUrlStreamIdentifier $fragment)
    {
        $this->id = $id;
        $this->authority = $authority;
        $this->path = $path;
        $this->args = $args;
        $this->fragment = $fragment;
    }

    public function __toString(): string
    {
        return $this->id;
    }

    public function getAssetAuthority(): FarahUrlAuthority
    {
        return $this->authority;
    }

    public function getAssetPath(): FarahUrlPath
    {
        return $this->path;
    }

    public function getArguments(): FarahUrlArguments // TOOD: rename to getQueryArguments
    {
        return $this->args;
    }

    public function getStreamIdentifier(): FarahUrlStreamIdentifier
    {
        return $this->fragment;
    }

    public function withAssetAuthority(FarahUrlAuthority $authority): FarahUrl
    {
        return self::createFromComponents($authority, $this->path, $this->args, $this->fragment);
    }

    public function withAssetPath(FarahUrlPath $path): FarahUrl
    {
        return self::createFromComponents($this->authority, $path, $this->args, $this->fragment);
    }

    public function withQueryArguments(FarahUrlArguments $args): FarahUrl
    {
        return self::createFromComponents($this->authority, $this->path, $args, $this->fragment);
    }

    public function withStreamIdentifier(FarahUrlStreamIdentifier $fragment)
    {
        return self::createFromComponents($this->authority, $this->path, $this->args, $fragment);
    }

    // UriInterface::with* functions:
    public function withScheme($scheme): FarahUrl
    {
        if ($scheme !== self::SCHEME_DEFAULT) {
            throw new ProtocolNotSupportedException($scheme);
        }
        return $this;
    }

    public function withUserInfo($user, $password = null): FarahUrl
    {
        if ($password !== null) {
            throw new MalformedUrlException($password);
        }
        return $this->withAssetAuthority(FarahUrlAuthority::createFromVendorAndModule((string) $user, $this->getHost()));
    }

    public function withHost($host): FarahUrl
    {
        return $this->withAssetAuthority(FarahUrlAuthority::createFromVendorAndModule($this->getUserInfo(), (string) $host));
    }

    public function withPort($port): FarahUrl
    {
        throw new MalformedUrlException($port);
    }

    public function withPath($path): FarahUrl
    {
        return $this->withAssetPath(FarahUrlPath::createFromString((string) $path));
    }

    public function withQuery($query): FarahUrl
    {
        return $this->withQueryArguments(FarahUrlArguments::createFromQuery((string) $query));
    }

    public function withFragment($fragment): FarahUrl
    {
        return $this->withStreamIdentifier(FarahUrlStreamIdentifier::createFromString((string) $fragment));
    }

    // UriInterface::get* functions:
    public function getScheme(): string
    {
        return $this->authority->getProtocol();
    }

    public function getAuthority(): string
    {
        return (string) $this->authority;
    }

    public function getUserInfo(): string
    {
        return $this->authority->getVendor();
    }

    public function getHost(): string
    {
        return $this->authority->getModule();
    }

    public function getPort(): int
    {
        return 0;
    }

    public function getPath(): string
    {
        return (string) $this->path;
    }

    public function getQuery(): string
    {
        return (string) $this->args;
    }

    public function getFragment(): string
    {
        return '';
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

