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
use Slothsoft\Farah\RequestStrategy\RequestStrategyInterface;
use Slothsoft\Farah\ResponseStrategy\ResponseStrategyInterface;

class Kernel
{
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
    
    private $requestStrategy;
    private $responseStrategy;
    
    public function __construct(RequestStrategyInterface $requestStrategy, ResponseStrategyInterface $responseStrategy) {
        $this->requestStrategy = $requestStrategy;
        $this->responseStrategy = $responseStrategy;
    }

    
    
    public function processPath(string $path)
    {
        $request = $this->createRequest($path, $_REQUEST, $_SERVER);
        
        $response = $this->createResponse($request);
        
        $this->requestStrategy->setRequest($request);
        
        try {
            $result = $this->requestStrategy->process();
            $file = $result->toFile();
            if (!$file->exists()) {
                throw new HttpStatusException("Failed to locate file '{$file->getName()}' ({$result->getUrl()}).", HTTPResponse::STATUS_NOT_FOUND);
            }
            $response->setFile($file->getPath(), $file->getName());
        } catch (HttpStatusException $e) {
            $response->setStatus($e->getCode(), $e->getMessage());
            foreach ($e->getAdditionalHeaders() as $key => $val) {
                $response->addHeader($key, $val);
            }
        }
        
        $this->responseStrategy->setResponse($response);
        return $this->responseStrategy->process();
    }
    
    private static function createRequest(string $path, array $req, array $env): HTTPRequest
    {
        $request = new HTTPRequest();
        $request->init($env);
        $request->setInput($req);
        $request->setAllHeaders(apache_request_headers());
        $request->setPath($path);
        
        return $request;
    }

    private static function createResponse(HTTPRequest $request)
    {
        $httpResponse = new HTTPResponse();
        $httpResponse->setRequest($request);
        
        return $httpResponse;
    }
}


