<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\RequestStrategy;

use Slothsoft\Farah\HTTPRequest;
use Slothsoft\Farah\HTTPResponse;
use Slothsoft\Farah\Module\Results\ResultInterface;
use Slothsoft\Farah\Security\BannedManager;
use Slothsoft\Farah\Exception\HttpStatusException;

abstract class RequestStrategyImplementation implements RequestStrategyInterface
{

    private $request;

    public function setRequest(HTTPRequest $request)
    {
        $this->request = $request;
    }

    public function getRequest(): HTTPRequest
    {
        return $this->request;
    }

    public function getDefaultVendor(): string
    {
        return 'slothsoft'; // TODO
    }

    public function getDefaultModule(): string
    {
        return 'farah'; // TODO
    }

    public function process() : ResultInterface
    {
        if (BannedManager::getInstance()->isBanned($this->request->clientIp)) {
            // BANHAMMER
            throw new HttpStatusException('You have been found wanting.', HTTPResponse::STATUS_PRECONDITION_FAILED);
        }
        
        if (! $this->request->protocolRecognised) {
            throw new HttpStatusException('', HTTPResponse::STATUS_BAD_REQUEST);
        }
        
        switch ($this->request->protocolName) {
            case HTTPRequest::PROTOCOL_HTTP:
                return $this->processHttp();
            default:
                throw new HttpStatusException('', HTTPResponse::STATUS_METHOD_NOT_ALLOWED);
        }
    }

    private function processHttp() : ResultInterface
    {
        if (! ($this->request->protocolMajorVersion >= 1 and $this->request->protocolMinorVersion >= 0)) {
            throw new HttpStatusException('', HTTPResponse::STATUS_HTTP_VERSION_NOT_SUPPORTED);
        }
        
        switch ($this->request->method) {
            case HTTPRequest::METHOD_HEAD:
            case HTTPRequest::METHOD_GET:
            case HTTPRequest::METHOD_POST:
                return $this->processHttpGet();
            default:
                throw new HttpStatusException('', HTTPResponse::STATUS_NOT_IMPLEMENTED);
        }
    }

    private function processHttpGet() : ResultInterface
    {
        return $this->loadResult();
    }

    abstract protected function loadResult(): ResultInterface;
}

