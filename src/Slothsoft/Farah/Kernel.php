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

use Slothsoft\Core\Configuration\ConfigurationField;
use Slothsoft\Farah\Configuration\AssetConfigurationField;
use Slothsoft\Farah\Exception\HttpStatusException;
use Slothsoft\Farah\Module\Node\Asset\AssetInterface;
use Slothsoft\Farah\RequestProcessor\RequestProcessorInterface;

class Kernel
{

    public static function getInstance(): Kernel
    {
        static $instance;
        if ($instance === null) {
            $instance = new Kernel();
        }
        return $instance;
    }

    private static function sitesAsset(): ConfigurationField
    {
        static $field;
        if ($field === null) {
            $field = new AssetConfigurationField();
        }
        return $field;
    }

    public static function setSitesAsset($asset)
    {
        self::sitesAsset()->setValue($asset);
    }

    public static function getSitesAsset(): AssetInterface
    {
        return self::sitesAsset()->getValue();
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

    public static function processPathRequest($path, RequestProcessorInterface $processor)
    {
        $kernel = self::getInstance();
        
        $request = $kernel->createRequest($path, $_REQUEST, $_SERVER);
        
        $response = $kernel->createResponse($request);
        
        $processor->setRequest($request);
        $processor->setResponse($response);
        
        try {
            $processor->process();
        } catch (HttpStatusException $e) {
            $response->setStatus($e->getCode(), $e->getMessage());
        }
        
        $response->send();
    }

    private function __construct()
    {}

    public function createRequest(string $path, array $req, array $env): HTTPRequest
    {
        $request = new HTTPRequest();
        $request->init($env);
        $request->setInput($req);
        $request->setAllHeaders(apache_request_headers());
        $request->setPath($path);
        
        $this->requestBackup = $request; // TODO: figure out where this belongs
        
        return $request;
    }

    public function createResponse(HTTPRequest $request)
    {
        $httpResponse = new HTTPResponse();
        $httpResponse->setRequest($request);
        
        return $httpResponse;
    }

    private $requestBackup;

    public function getRequest(): HTTPRequest
    {
        if ($this->requestBackup) {
            $ret = $this->requestBackup;
        } else {
            $ret = new HTTPRequest();
            $ret->init([]);
        }
        return $ret;
    }
}


