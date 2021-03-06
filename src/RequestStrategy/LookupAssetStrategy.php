<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\RequestStrategy;

use Psr\Http\Message\ServerRequestInterface;
use Slothsoft\Core\Configuration\ConfigurationField;
use Slothsoft\Farah\FarahUrl\FarahUrl;
use Slothsoft\Farah\FarahUrl\FarahUrlArguments;
use Slothsoft\Farah\FarahUrl\FarahUrlAuthority;

class LookupAssetStrategy extends RequestStrategyBase {

    const DEFAULT_VENDOR = 'slothsoft';

    const DEFAULT_MODULE = 'farah';

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

    protected function createUrl(ServerRequestInterface $request): FarahUrl {
        $uri = $request->getUri();
        $body = $request->getParsedBody();
        $params = $request->getQueryParams();

        if (is_array($body)) {
            $args = $body + $params;
        } else {
            $args = $params;
        }

        if ($uri instanceof FarahUrl) {
            $url = $uri;
        } else {
            $urlAuthority = FarahUrlAuthority::createFromVendorAndModule(self::DEFAULT_VENDOR, self::DEFAULT_MODULE);
            $urlPath = null;
            $urlArgs = FarahUrlArguments::createFromValueList($args);

            $url = FarahUrl::createFromReference($this->extractFarahUrl($uri->getPath()), FarahUrl::createFromComponents($urlAuthority, $urlPath, $urlArgs));
        }

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

