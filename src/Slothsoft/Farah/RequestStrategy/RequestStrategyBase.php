<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\RequestStrategy;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slothsoft\Core\Calendar\Seconds;
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
use GuzzleHttp\Psr7\StreamWrapper;

abstract class RequestStrategyBase implements RequestStrategyInterface
{

    private $request;

    public function process(ServerRequestInterface $request): ResponseInterface
    {
        try {
            $this->request = $request;
            
            $this->validateRequest();
            $url = $this->createUrl($request);
            
            try {
                $result = FarahUrlResolver::resolveToResult($url);
                
                $statusCode = StatusCode::STATUS_OK;
                
                $headers = [];
                $headers['vary'] = 'accept-encoding';
                
                $fileDisposition = 'inline';
                $fileName = $result->lookupFileName();
                
                $headers['content-disposition'] = sprintf('%s; filename="%s"; filename*=UTF-8\'\'%s', $fileDisposition, preg_replace('/[^[:print:]]/', '', $fileName), rawurlencode($fileName));
                
                $fileMime = $result->lookupMimeType();
                $fileCharset = $result->lookupCharset();
                $headers['content-type'] = $fileCharset === ''
                    ? $fileMime
                    : "$fileMime; charset=$fileCharset";
                
                $cacheDuration = $this->inventCacheDuration($fileMime);
                $headers['cache-control'] = "must-revalidate, max-age=$cacheDuration";
                
                $contentCoding = $this->negotiateContentCoding();
                $transferCoding = $this->negotiateTransferCoding();
                
                $fileTime = $result->lookupChangeTime();
                if ($fileTime > 0) {
                    $headers['last-modified'] = gmdate('D, d M Y H:i:s \\G\\M\\T', $fileTime);
                }
                
                $fileHash = $result->lookupHash();
                if ($fileHash !== '') {
                    $headers['etag'] = "\"$fileHash-$contentCoding\"";
                }
                
                
                $body = $result->lookupStream();
                $resource = StreamWrapper::getResource($body);
                
                if (! $contentCoding->isNoEncoding()) {
                    stream_filter_append($resource, $contentCoding->getFilterName(), STREAM_FILTER_READ);
                    $headers['content-encoding'] = $contentCoding->getHttpName();
                }
                
                if (! $transferCoding->isNoEncoding()) {
                    stream_filter_append($resource, $transferCoding->getFilterName(), STREAM_FILTER_READ);
                    $headers['transfer-encoding'] = $transferCoding->getHttpName();
                }
                
                if (isset($headers['last-modified']) and $request->hasHeader('if-modified-since')) {
                    $serverTime = (int) strtotime($headers['last-modified']);
                    $clientTime = (int) strtotime($request->getHeaderLine('if-modified-since'));
                    if ($clientTime >= $serverTime) {
                        throw new HttpStatusException('', StatusCode::STATUS_NOT_MODIFIED, null, $headers);
                    }
                }
                
                if (isset($headers['etag']) and $request->hasHeader('if-none-match')) {
                    $serverTag = $headers['etag'];
                    $clientTag = $request->getHeaderLine('if-none-match');
                    if ($serverTag === $clientTag) {
                        throw new HttpStatusException('', StatusCode::STATUS_NOT_MODIFIED, null, $headers);
                    }
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
        
        if (! $this->shouldIncludeBody($this->request->getMethod(), $statusCode)) {
            $body->detach();
        }
        
        return MessageFactory::createServerResponse($statusCode, $headers, $body);
    }

    private function validateRequest()
    {
        $clientIp = $this->request->getServerParams()['REMOTE_ADDR'] ?? '';
        if (strlen($clientIp) and BannedManager::getInstance()->isBanned($clientIp)) {
            // BANHAMMER
            throw new HttpStatusException('You have been found wanting.', StatusCode::STATUS_PRECONDITION_FAILED);
        }
        
        if (! in_array($this->request->getUri()->getScheme(), [
            'http',
            'farah'
        ])) {
            throw new HttpStatusException("Scheme '{$this->request->getUri()->getScheme()}' is not supported by this implementation.", StatusCode::STATUS_NOT_IMPLEMENTED);
        }
        
        if (! in_array($this->request->getMethod(), [
            'HEAD',
            'GET',
            'POST'
        ])) {
            throw new HttpStatusException("HTTP Method '{$this->request->getMethod()}' is not supported by this implementation.", StatusCode::STATUS_METHOD_NOT_ALLOWED);
        }
    }

    abstract protected function createUrl(ServerRequestInterface $request): FarahUrl;

    private function negotiateContentCoding(): ContentCoding
    {
        $accept = $this->request->getHeaderLine('accept-encoding');
        foreach (ContentCoding::values() as $coding) {
            if ($coding->isAvailable() and strpos($accept, $coding->getHttpName()) !== false) {
                return $coding;
            }
        }
        return ContentCoding::identity();
    }

    private function negotiateTransferCoding(): TransferCoding
    {
        $accept = $this->request->getHeaderLine('te');
        if (version_compare($this->request->getProtocolVersion(), '1.1', '>=')) {
            $accept .= ', chunked'; // HTTP 1.1 must accept chunked encoding
        }
        foreach (TransferCoding::values() as $coding) {
            if ($coding->isAvailable() and strpos($accept, $coding->getHttpName()) !== false) {
                return $coding;
            }
        }
        return TransferCoding::identity();
    }

    private function shouldIncludeBody(string $method, int $statusCode): bool
    {
        switch ($method) {
            case 'HEAD':
                return false;
        }
        
        switch ($statusCode) {
            case StatusCode::STATUS_NO_CONTENT:
            case StatusCode::STATUS_MULTIPLE_CHOICES:
            case StatusCode::STATUS_MOVED_PERMANENTLY:
            case StatusCode::STATUS_SEE_OTHER:
            case StatusCode::STATUS_NOT_MODIFIED:
            case StatusCode::STATUS_TEMPORARY_REDIRECT:
            case StatusCode::STATUS_PERMANENT_REDIRECT:
                return false;
        }
        
        return true;
    }
    
    private function inventCacheDuration(string $mimeType) : int {
        if (strpos($mimeType, 'image/') === 0) {
            return Seconds::MONTH;
        }
        if (strpos($mimeType, 'application/font') === 0) {
            return Seconds::YEAR;
        }
        if (strpos($mimeType, 'application/javascript') === 0) {
            return Seconds::WEEK;
        }
        if (strpos($mimeType, 'text/css') === 0) {
            return Seconds::WEEK;
        }
        return 30;
    }
}

