<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\RequestStrategy;

use Psr\Http\Message\ServerRequestInterface;
use Slothsoft\Core\Configuration\ConfigurationField;
use Slothsoft\Farah\Module\FarahUrl\FarahUrl;
use Slothsoft\Farah\Module\FarahUrl\FarahUrlArguments;
use Slothsoft\Farah\Module\FarahUrl\FarahUrlAuthority;

class LookupAssetStrategy extends RequestStrategyBase
{
    const DEFAULT_VENDOR = 'slothsoft';
    const DEFAULT_MODULE = 'farah';
    
    private static function hrefBase(): ConfigurationField
    {
        static $field;
        if ($field === null) {
            $field = new ConfigurationField('/getAsset.php/');
        }
        return $field;
    }
    
    public static function setHrefBase($hrefBase)
    {
        self::hrefBase()->setValue($hrefBase);
    }
    
    public static function getHrefBase(): string
    {
        return self::hrefBase()->getValue();
    }

    protected function createUrl(ServerRequestInterface $request): FarahUrl
    {
        $uri = $request->getUri();
        $args = $request->getQueryParams();
        
        
        if ($uri instanceof FarahUrl) {
            $url = $uri;
        } else {
            $url = FarahUrl::createFromReference(
                $this->extractFarahUrl($uri->getPath()),
                FarahUrlAuthority::createFromVendorAndModule(self::DEFAULT_VENDOR, self::DEFAULT_MODULE),
                null,
                FarahUrlArguments::createFromValueList($args)
            );
        }
        
        return $url;
    }
    
    private function extractFarahUrl(string $path) : string {
        if (strpos($path, self::getHrefBase()) === 0) {
            $path = substr($path, strlen(self::getHrefBase()));
        }
        
        if (strpos($path, 'farah://') !== 0) {
            $path = "farah://$path";
        }
        return $path;
    }
}

