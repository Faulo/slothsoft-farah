<?php
declare(strict_types = 1);

namespace Slothsoft\Farah\RequestStrategy;

use Psr\Http\Message\ServerRequestInterface;
use Slothsoft\Core\Configuration\ConfigurationField;
use Slothsoft\Farah\FarahUrl\FarahUrl;
use Slothsoft\Farah\FarahUrl\FarahUrlArguments;
use Slothsoft\Farah\Module\Module;

/**
 * Request strategy that resolves an incoming request directly to a Farah asset URL.
 *
 * @author Daniel Schulz
 * @since 2018-04-17
 */
final class LookupAssetStrategy extends RequestStrategyBase {
    
    private static function hrefBase(): ConfigurationField {
        static $field;
        if ($field === null) {
            $field = new ConfigurationField('/');
        }
        return $field;
    }
    
    public static function setHrefBase($hrefBase): void {
        self::hrefBase()->setValue($hrefBase);
    }
    
    public static function getHrefBase(): string {
        return self::hrefBase()->getValue();
    }
    
    public function createUrl(ServerRequestInterface $request): FarahUrl {
        $uri = $request->getUri();
        $body = $request->getParsedBody();
        $params = $request->getQueryParams();
        
        if ($uri instanceof FarahUrl) {
            $url = $uri;
        } else {
            $url = FarahUrl::createFromReference($this->extractFarahUrl($uri->getPath()), Module::getBaseUrl());
        }
        
        if (is_array($body)) {
            $args = $body + $params;
        } else {
            $args = $params;
        }
        
        return $url->withQueryArguments(FarahUrlArguments::createFromValueList($args));
    }
    
    private function extractFarahUrl(string $path): string {
        if (str_starts_with($path, self::getHrefBase())) {
            $path = substr($path, strlen(self::getHrefBase()));
        }
        
        if ($path[0] === '/') {
            $path = "farah:/$path";
        }
        
        if (! str_starts_with($path, 'farah://')) {
            $path = "farah://$path";
        }
        
        return urldecode($path);
    }
}

