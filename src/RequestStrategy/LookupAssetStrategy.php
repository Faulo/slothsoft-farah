<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\RequestStrategy;

use Psr\Http\Message\ServerRequestInterface;
use Slothsoft\Core\Configuration\ConfigurationField;
use Slothsoft\Farah\FarahUrl\FarahUrl;
use Slothsoft\Farah\FarahUrl\FarahUrlArguments;
use Slothsoft\Farah\Module\Module;

class LookupAssetStrategy extends RequestStrategyBase {

    private static function hrefBase(): ConfigurationField {
        static $field;
        if ($field === null) {
            $field = new ConfigurationField('/');
        }
        return $field;
    }

    public static function setHrefBase($hrefBase) {
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

        $url = $url->withQueryArguments(FarahUrlArguments::createFromValueList($args));

        return $url;
    }

    private function extractFarahUrl(string $path): string {
        if (strpos($path, self::getHrefBase()) === 0) {
            $path = substr($path, strlen(self::getHrefBase()));
        }

        if ($path[0] === '/') {
            $path = "farah:/$path";
        }

        if (strpos($path, 'farah://') !== 0) {
            $path = "farah://$path";
        }

        $path = urldecode($path);

        return $path;
    }
}

