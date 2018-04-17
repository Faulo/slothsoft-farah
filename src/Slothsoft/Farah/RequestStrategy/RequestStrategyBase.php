<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\RequestStrategy;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slothsoft\Core\MimeTypeDictionary;
use Slothsoft\Farah\Exception\AssetPathNotFoundException;
use Slothsoft\Farah\Exception\HttpStatusException;
use Slothsoft\Farah\Exception\ModuleNotFoundException;
use Slothsoft\Farah\Http\ContentCoding;
use Slothsoft\Farah\Http\MessageFactory;
use Slothsoft\Farah\Http\StatusCode;
use Slothsoft\Farah\Http\TransferCoding;
use Slothsoft\Farah\Module\FarahUrl\FarahUrl;
use Slothsoft\Farah\Module\FarahUrl\FarahUrlResolver;
use Slothsoft\Farah\Security\BannedManager;

abstract class RequestStrategyBase implements RequestStrategyInterface
{
    private $request;
    private $negotiatedContentCodings;
    private $negotiatedTransferCodings;
    
    public function process(ServerRequestInterface $request) : ResponseInterface
    {
        try {
            $this->request = $request;
            
            $this->validateRequest();
            $url = $this->createUrl($request);
            
            try {
                $result = FarahUrlResolver::resolveToResult($url);
                
                $statusCode = StatusCode::STATUS_OK;
                $file = $result->toFile();
                $fileName = $file->getName();
                $fileDisposition = 'inline';
                $fileEncoding = 'UTF-8';
                $headers = [];
                $headers['content-disposition'] = sprintf(
                    '%s; filename="%s"; filename*=UTF-8\'\'%s',
                    $fileDisposition,
                    preg_replace('/[^[:print:]]/', '', $fileName),
                    rawurlencode($fileName)
                );
                $headers['content-type'] = sprintf(
                    '%s; charset=%s',
                    MimeTypeDictionary::guessMime(pathinfo($fileName, PATHINFO_EXTENSION)),
                    $fileEncoding
                );
                
                $body = MessageFactory::createStreamFromUrl($url);
                $resource = $body->detach();
                
                if ($body->isSeekable()) {
                    $hash = hash_init('md5');
                    hash_update_stream($hash, $resource);
                    $responseTag = '"'.hash_final($hash).'"';
                    
                    $headers['etag'] = $responseTag;
                    
                    $requestTag = $request->getHeaderLine('if-none-match');
                    if ($requestTag === $responseTag) {
                        throw new HttpStatusException('', StatusCode::STATUS_NOT_MODIFIED, null, ['etag' => $responseTag]);
                    } else {
                        rewind($resource);
                    }
                }
                
                $this->negotiateContentCodings();
                foreach ($this->negotiatedContentCodings as $coding) {
                    stream_filter_append($resource, $coding->getFilterName(), STREAM_FILTER_READ);
                    $headers['content-encoding'] = $coding->getHttpName();
                    $headers['vary'] = 'accept-encoding';
                }
                
                $this->negotiateTransferCodings();
                foreach ($this->negotiatedTransferCodings as $coding) {
                    stream_filter_append($resource, $coding->getFilterName(), STREAM_FILTER_READ);
                    $headers['transfer-encoding'] = $coding->getHttpName();
                }
                
                $body = MessageFactory::createStreamFromResource($resource);
            } catch (ModuleNotFoundException $e) {
                throw new HttpStatusException($e->getMessage(), StatusCode::STATUS_NOT_FOUND, $e);
            } catch (AssetPathNotFoundException $e) {
                throw new HttpStatusException($e->getMessage(), StatusCode::STATUS_NOT_FOUND, $e);
            }
        } catch (HttpStatusException $e) {
            $statusCode = $e->getCode();
            $headers = $e->getAdditionalHeaders();
            $body = MessageFactory::createStreamFromContents(StatusCode::getMessage($statusCode, $e->getMessage()));
        }
        
        return MessageFactory::createServerResponse(
            $statusCode,
            $headers,
            $body
        );
    }

    private function validateRequest()
    {
        $clientIp = $this->request->getServerParams()['REMOTE_ADDR'] ?? '';
        if (strlen($clientIp) and BannedManager::getInstance()->isBanned($clientIp)) {
            // BANHAMMER
            throw new HttpStatusException('You have been found wanting.', StatusCode::STATUS_PRECONDITION_FAILED);
        }
        
        if (!in_array($this->request->getUri()->getScheme(), ['http', 'farah'])) {
            throw new HttpStatusException("Scheme '{$this->request->getUri()->getScheme()}' is not supported by this implementation.", StatusCode::STATUS_NOT_IMPLEMENTED);
        }
        
        if (!in_array($this->request->getMethod(), ['HEAD', 'GET', 'POST'])) {
            throw new HttpStatusException("HTTP Method '{$this->request->getMethod()}' is not supported by this implementation.", StatusCode::STATUS_METHOD_NOT_ALLOWED);
        }
    }
    
    abstract protected function createUrl(ServerRequestInterface $request): FarahUrl;
    
    private function negotiateContentCodings() {
        $this->negotiatedContentCodings = [];
        $accept = $this->request->getHeaderLine('accept-encoding');
        foreach (ContentCoding::values() as $coding) {
            if ($coding->isAvailable() and strpos($accept, $coding->getHttpName()) !== false) {
                //$this->negotiatedContentCodings[] = $coding;
                break;
            }
        }
    }
    
    private function negotiateTransferCodings() {
        $this->negotiatedTransferCodings = [];
        $accept = $this->request->getHeaderLine('te');
        if (version_compare($this->request->getProtocolVersion(), '1.1', '>=')) {
            $accept .= ', chunked'; //HTTP 1.1 must accept chunked encoding
        }
        foreach (TransferCoding::values() as $coding) {
            if ($coding->isAvailable() and strpos($accept, $coding->getHttpName()) !== false) {
                //$this->negotiatedTransferCodings[] = $coding;
                break;
            }
        }
    }
}

