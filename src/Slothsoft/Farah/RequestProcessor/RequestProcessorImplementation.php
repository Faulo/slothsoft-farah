<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\RequestProcessor;

use Slothsoft\Farah\HTTPRequest;
use Slothsoft\Farah\HTTPResponse;
use Slothsoft\Farah\Module\Results\ResultInterface;
use Slothsoft\Farah\Security\BannedManager;
use Slothsoft\Farah\Exception\HttpStatusException;

abstract class RequestProcessorImplementation implements RequestProcessorInterface
{

    protected $request;

    protected $response;

    public function setRequest(HTTPRequest $request)
    {
        $this->request = $request;
    }

    public function getRequest(): HTTPRequest
    {
        return $this->request;
    }

    public function setResponse(HTTPResponse $response)
    {
        $this->response = $response;
    }

    public function getResponse(): HTTPResponse
    {
        return $this->response;
    }

    public function getDefaultVendor(): string
    {
        return 'slothsoft'; // TODO
    }

    public function getDefaultModule(): string
    {
        return 'farah'; // TODO
    }

    public function process()
    {
        if (BannedManager::getInstance()->isBanned($this->request->clientIp)) {
            // BANHAMMER
            $this->response->setStatus(HTTPResponse::STATUS_PRECONDITION_FAILED, 'You have been found wanting.');
            return;
        }
        
        if (! $this->request->protocolRecognised) {
            $this->response->setStatus(HTTPResponse::STATUS_BAD_REQUEST);
            return;
        }
        
        switch ($this->request->protocolName) {
            case HTTPRequest::PROTOCOL_HTTP:
                $this->processHttp();
                return;
            default:
                $this->response->setStatus(HTTPResponse::STATUS_METHOD_NOT_ALLOWED);
                return;
        }
    }

    private function processHttp()
    {
        if (! ($this->request->protocolMajorVersion >= 1 and $this->request->protocolMinorVersion >= 0)) {
            $this->response->setStatus(HTTPResponse::STATUS_HTTP_VERSION_NOT_SUPPORTED);
            return;
        }
        
        switch ($this->request->method) {
            case HTTPRequest::METHOD_HEAD:
            case HTTPRequest::METHOD_GET:
            case HTTPRequest::METHOD_POST:
                $this->processHttpGet();
                return;
            default:
                $this->response->setStatus(HTTPResponse::STATUS_NOT_IMPLEMENTED);
                return;
        }
    }

    private function processHttpGet()
    {
        $this->response->setStatus(HTTPResponse::STATUS_GONE);
        $this->response->setDownload(isset($this->request->input['download']));
        
        $result = $this->loadResult();
        
        $file = $result->toFile();
        if (!$file->exists()) {
            throw new HttpStatusException("Failed to locate file '{$file->getName()}'.", HTTPResponse::STATUS_NOT_FOUND);
        }
        $this->response->setFile($file->getPath(), $file->getName());
    }

    abstract protected function loadResult(): ResultInterface;
}

