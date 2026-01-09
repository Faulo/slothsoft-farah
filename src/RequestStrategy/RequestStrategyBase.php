<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\RequestStrategy;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slothsoft\Core\MimeTypeDictionary;
use Slothsoft\Core\IO\Psr7\StreamHelper;
use Slothsoft\Farah\HTTPRequest;
use Slothsoft\Farah\HTTPResponse;
use Slothsoft\Farah\Kernel;
use Slothsoft\Farah\Exception\AssetPathNotFoundException;
use Slothsoft\Farah\Exception\HttpDownloadException;
use Slothsoft\Farah\Exception\HttpStatusException;
use Slothsoft\Farah\Exception\ModuleNotFoundException;
use Slothsoft\Farah\FarahUrl\FarahUrl;
use Slothsoft\Farah\Http\CodingInterface;
use Slothsoft\Farah\Http\ContentCoding;
use Slothsoft\Farah\Http\MessageFactory;
use Slothsoft\Farah\Http\StatusCode;
use Slothsoft\Farah\Http\TransferCoding;
use Slothsoft\Farah\Module\Module;
use Slothsoft\Farah\Security\BannedManager;

abstract class RequestStrategyBase implements RequestStrategyInterface {
    
    private ServerRequestInterface $request;
    
    public function process(ServerRequestInterface $request): ResponseInterface {
        try {
            $this->request = $request;
            
            $this->validateRequest();
            $url = $this->createUrl($request);
            
            Kernel::setCurrentPage($url);
            
            try {
                try {
                    $result = Module::resolveToResult($url);
                    $fileDisposition = 'inline';
                    $fileName = $result->lookupFileName();
                    $fileMime = $result->lookupMimeType();
                    $fileCharset = $result->lookupCharset();
                    $fileTime = $result->lookupFileStatistics()['mtime'] ?? 0;
                    $fileHash = $result->lookupHash();
                    $isBufferable = $result->lookupIsBufferable();
                    $isCompressable = $isBufferable; // $result->lookupIsCompressable();
                    $body = $result->lookupStreamWriter()->toStream();
                } catch (HttpDownloadException $e) {
                    $result = $e->getResult();
                    $fileDisposition = $e->isInline() ? 'inline' : 'download';
                    $fileName = $result->lookupFileName();
                    $fileMime = $result->lookupMimeType();
                    $fileCharset = $result->lookupCharset();
                    $fileTime = $result->lookupFileStatistics()['mtime'] ?? 0;
                    $fileHash = $result->lookupHash();
                    $isBufferable = $result->lookupIsBufferable();
                    $isCompressable = $isBufferable; // $result->lookupIsCompressable();
                    $body = $result->lookupStreamWriter()->toStream();
                }
                $statusCode = StatusCode::STATUS_OK;
                
                $headers = [];
                
                if ($fileName === '') {
                    $fileName = uniqid();
                }
                
                $headers['content-disposition'] = sprintf('%s; filename="%s"; filename*=UTF-8\'\'%s', $fileDisposition, preg_replace('/[^[:print:]]/', '', $fileName), rawurlencode($fileName));
                
                if ($fileMime === '') {
                    $fileMime = 'application/octet-stream';
                }
                
                $headers['content-type'] = $fileCharset === '' ? $fileMime : "$fileMime; charset=$fileCharset";
                
                $cacheDuration = HTTPResponse::inventCacheDuration($fileMime);
                $headers['cache-control'] = "must-revalidate, max-age=$cacheDuration";
                
                if ($fileTime > 0) {
                    $headers['last-modified'] = gmdate('D, d M Y H:i:s \\G\\M\\T', $fileTime);
                }
                
                if ($isCompressable) {
                    $preferredCompressions = $this->inventPreferredCompressions($fileMime);
                    if (strlen($preferredCompressions)) {
                        $contentCoding = $this->negotiateContentCoding($preferredCompressions);
                        $body = $contentCoding->encodeStream($body);
                        $contentCodingName = $contentCoding->getHttpName();
                        $headers['vary'] = 'accept-encoding';
                        if ($contentCodingName !== '') {
                            if ($fileHash !== '') {
                                $fileHash .= "-$contentCodingName";
                            }
                            $headers['content-encoding'] = $contentCodingName;
                        }
                    }
                }
                
                if ($fileHash !== '') {
                    $headers['etag'] = "\"$fileHash\"";
                }
                
                $bodyLength = $body->getSize();
                
                if ($bodyLength === null and $isBufferable) {
                    $body = StreamHelper::cacheStream($body);
                    $bodyLength = $body->getSize();
                }
                
                if ($bodyLength === null) {
                    // we don't know the length of the response, so we better figure out a safe transfer coding
                    $transferCoding = $this->negotiateTransferCoding('chunked');
                    $body = $transferCoding->encodeStream($body);
                    $transferCodingName = $transferCoding->getHttpName();
                    if ($transferCodingName !== '') {
                        $headers['transfer-encoding'] = $transferCodingName;
                    }
                } else {
                    $headers['accept-ranges'] = 'bytes';
                    if ($request->hasHeader('range')) {
                        $match = [];
                        if (preg_match('/^bytes=(\d*)-(\d*)(.*)$/', $request->getHeaderLine('range'), $match)) {
                            if (strlen($match[3])) {
                                throw new HttpStatusException('', StatusCode::STATUS_REQUESTED_RANGE_NOT_SATISFIABLE, null, $headers);
                            }
                            $rangeStart = strlen($match[1]) ? (int) $match[1] : 0;
                            $rangeEnd = strlen($match[2]) ? (int) $match[2] + 1 : $bodyLength;
                            
                            $statusCode = StatusCode::STATUS_PARTIAL_CONTENT;
                            $headers['content-range'] = sprintf('bytes %1$d-%2$d/%3$d', $rangeStart, $rangeEnd - 1, $bodyLength);
                            
                            $bodyLength = $rangeEnd - $rangeStart;
                            
                            $body = StreamHelper::sliceStream($body, $rangeStart, $bodyLength);
                        }
                    }
                    $headers['content-length'] = $bodyLength;
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
            } catch (ModuleNotFoundException $e) {
                throw new HttpStatusException($e->getMessage(), StatusCode::STATUS_NOT_FOUND, $e);
            } catch (AssetPathNotFoundException $e) {
                throw new HttpStatusException($e->getMessage(), StatusCode::STATUS_NOT_FOUND, $e);
            }
        } catch (HttpStatusException $e) {
            $statusCode = $e->getCode();
            $headers = $e->getAdditionalHeaders();
            $body = MessageFactory::createStreamFromContents(StatusCode::getMessage($statusCode, $e->getMessage()) . PHP_EOL);
        }
        
        if (! $this->shouldIncludeBody($this->request->getMethod(), $statusCode)) {
            $body = null;
        }
        
        return MessageFactory::createServerResponse($statusCode, $headers, $body);
    }
    
    private function validateRequest(): void {
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
            HTTPRequest::METHOD_HEAD,
            HTTPRequest::METHOD_GET,
            HTTPRequest::METHOD_POST,
            HTTPRequest::METHOD_OPTIONS
        ])) {
            throw new HttpStatusException("HTTP Method '{$this->request->getMethod()}' is not supported by this implementation.", StatusCode::STATUS_METHOD_NOT_ALLOWED);
        }
    }
    
    abstract public function createUrl(ServerRequestInterface $request): FarahUrl;
    
    private function negotiateContentCoding(string $preferred): CodingInterface {
        $accept = $this->request->getHeaderLine('accept-encoding');
        foreach (ContentCoding::getEncodings() as $name => $coding) {
            if (strpos($preferred, $name) !== false and strpos($accept, $name) !== false) {
                return $coding;
            }
        }
        return ContentCoding::identity();
    }
    
    private function negotiateTransferCoding(string $preferred): CodingInterface {
        $accept = $this->request->getHeaderLine('te');
        if (version_compare($this->request->getProtocolVersion(), '1.1', '>=')) {
            $accept .= ', chunked'; // HTTP 1.1 must accept chunked encoding
        }
        foreach (TransferCoding::getEncodings() as $name => $coding) {
            if (strpos($preferred, $name) !== false and strpos($accept, $name) !== false) {
                return $coding;
            }
        }
        return TransferCoding::identity();
    }
    
    private function shouldIncludeBody(string $method, int $statusCode): bool {
        switch ($method) {
            case HTTPRequest::METHOD_HEAD:
            case HTTPRequest::METHOD_OPTIONS:
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
    
    private function inventPreferredCompressions(string $mimeType): string {
        return MimeTypeDictionary::guessCompressions($mimeType);
    }
}

