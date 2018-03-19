<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\RequestProcessor;

use Slothsoft\Farah\HTTPRequest;
use Slothsoft\Farah\HTTPResponse;
use Slothsoft\Farah\LinkDecorator\DecoratorFactory;
use Slothsoft\Farah\Module\Results\ResultInterface;
use Slothsoft\Farah\Module\Results\TransformationResult;
use Slothsoft\Farah\Security\BannedManager;

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
        
        if ($result instanceof TransformationResult) {
            $document = $result->toDocument();
            if ($document->documentElement) {
                $stylesheetList = $result->getLinkedStylesheets();
                $scriptList = $result->getLinkedScripts();
                if ($stylesheetList or $scriptList) {
                    $decorator = DecoratorFactory::createForDocument($document);
                    $decorator->linkStylesheets(...$stylesheetList);
                    $decorator->linkScripts(...$scriptList);
                }
            }
            $this->response->setDocument($document);
        } else {
            $file = $result->toFile();
            $this->response->setFile($file->getPath(), $file->getName());
        }
    }

    abstract protected function loadResult(): ResultInterface;
}

