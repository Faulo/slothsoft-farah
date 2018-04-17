<?php
declare(strict_types = 1);
/**
 * *********************************************************************
 * Slothsoft\Farah\Kernel v1.00 19.10.2012 © Daniel Schulz
 *
 * Changelog:
 * v1.00 19.10.2012
 * initial release
 * *********************************************************************
 */
namespace Slothsoft\Farah;

use Psr\Http\Message\ServerRequestInterface;
use Slothsoft\Core\Configuration\ConfigurationField;
use Slothsoft\Farah\Configuration\AssetConfigurationField;
use Slothsoft\Farah\Module\Node\Asset\AssetInterface;
use Slothsoft\Farah\RequestStrategy\RequestStrategyInterface;
use Slothsoft\Farah\ResponseStrategy\ResponseStrategyInterface;

class Kernel
{

    const URL_REQUEST = 'farah://slothsoft@farah/request';

    const URL_SITEMAP = 'farah://slothsoft@farah/sites';

    private static function currentSitemap(): ConfigurationField
    {
        static $field;
        if ($field === null) {
            $field = new AssetConfigurationField();
        }
        return $field;
    }

    public static function setCurrentSitemap($asset)
    {
        self::currentSitemap()->setValue($asset);
    }

    public static function getCurrentSitemap(): AssetInterface
    {
        return self::currentSitemap()->getValue();
    }

    private static function currentRequest(): ConfigurationField
    {
        static $field;
        if ($field === null) {
            $field = new ConfigurationField();
        }
        return $field;
    }

    public static function setCurrentRequest(ServerRequestInterface $request)
    {
        self::currentRequest()->setValue($request);
    }

    public static function getCurrentRequest(): ServerRequestInterface
    {
        return self::currentRequest()->getValue();
    }

    private static function trackingEnabled(): ConfigurationField
    {
        static $field;
        if ($field === null) {
            $field = new ConfigurationField(false);
        }
        return $field;
    }

    public static function setTrackingEnabled(bool $value)
    {
        self::trackingEnabled()->setValue($value);
    }

    public static function getTrackingEnabled(): bool
    {
        return self::trackingEnabled()->getValue();
    }

    private static function trackingExceptionUris()
    {
        static $field;
        if ($field === null) {
            $field = new ConfigurationField([]);
        }
        return $field;
    }

    public static function setTrackingExceptionUris(string ...$uriList)
    {
        self::trackingExceptionUris()->setValue($uriList);
    }

    public static function getTrackingExceptionUris(): array
    {
        return self::trackingExceptionUris()->getValue();
    }

    private $requestStrategy;

    private $responseStrategy;

    public function __construct(RequestStrategyInterface $requestStrategy, ResponseStrategyInterface $responseStrategy)
    {
        $this->requestStrategy = $requestStrategy;
        $this->responseStrategy = $responseStrategy;
    }

    public function handle(ServerRequestInterface $request)
    {
        self::setCurrentRequest($request);
        
        $response = $this->requestStrategy->process($request);
        $this->responseStrategy->process($response);
    }
}


