<?php
declare(strict_types = 1);

namespace Slothsoft\Farah\RequestStrategy;

use DateTimeImmutable;
use DateTimeZone;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slothsoft\Core\IO\Psr7\StreamHelper;
use Slothsoft\Core\MimeTypeDictionary;
use Slothsoft\Farah\Exception\AssetPathNotFoundException;
use Slothsoft\Farah\Exception\HttpDownloadException;
use Slothsoft\Farah\Exception\HttpStatusException;
use Slothsoft\Farah\Exception\ModuleNotFoundException;
use Slothsoft\Farah\FarahUrl\FarahUrl;
use Slothsoft\Farah\Http\CodingInterface;
use Slothsoft\Farah\Http\ContentCoding;
use Slothsoft\Farah\Http\MessageFactory;
use Slothsoft\Farah\Http\Range\ByteCount;
use Slothsoft\Farah\Http\Range\RangeRequest;
use Slothsoft\Farah\Http\Range\RangeStream;
use Slothsoft\Farah\Http\StatusCode;
use Slothsoft\Farah\Http\TransferCoding;
use Slothsoft\Farah\Http\WebScheme;
use Slothsoft\Farah\HTTPRequest;
use Slothsoft\Farah\HTTPResponse;
use Slothsoft\Farah\Kernel;
use Slothsoft\Farah\Module\Module;
use Slothsoft\Farah\Security\BannedManager;

/**
 * Base class for request strategies that convert Farah URLs into PSR-7 responses.
 *
 * @author Daniel Schulz
 * @since 2018-04-17
 */
abstract class RequestStrategyBase implements RequestStrategyInterface {
    
    private ServerRequestInterface $request;
    
    public function process(ServerRequestInterface $request): ResponseInterface {
        try {
            $this->request = $request;
            
            $this->validateRequest();
            $url = $this->createUrl($request);
            
            Kernel::setCurrentPage($url);
            
            $statusCode = StatusCode::STATUS_OK;
            
            try {
                try {
                    $result = Module::resolveToResult($url);
                    $fileDisposition = 'inline';
                    
                    $fileName = $result->lookupFileName();
                    $fileMime = $result->lookupMimeType();
                    $fileCharset = $result->lookupCharset();
                    $fileTime = $result->lookupFileChangeTime();
                    $fileHash = $result->lookupHash();
                    $isBufferable = $result->lookupIsBufferable();
                    $isCompressable = $isBufferable;
                    $body = $result->lookupStreamWriter()->toStream();
                } catch (HttpDownloadException $e) {
                    $result = $e->getResult();
                    $fileDisposition = $e->isInline() ? 'inline' : 'download';
                    
                    $fileName = $result->lookupFileName();
                    $fileMime = $result->lookupMimeType();
                    $fileCharset = $result->lookupCharset();
                    $fileTime = $result->lookupFileChangeTime();
                    $fileHash = $result->lookupHash();
                    $isBufferable = $result->lookupIsBufferable();
                    $isCompressable = $isBufferable;
                    $body = $result->lookupStreamWriter()->toStream();
                }
                
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
                
                $isEncoded = false;
                if ($isCompressable) {
                    $preferredCompressions = $this->inventPreferredCompressions($fileMime);
                    if (strlen($preferredCompressions)) {
                        $contentCoding = $this->negotiateContentCoding($preferredCompressions);
                        $body = $contentCoding->encodeStream($body);
                        $contentCodingName = $contentCoding->getHttpName();
                        $headers['vary'] = 'accept-encoding';
                        if ($contentCodingName !== '') {
                            $isEncoded = true;
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
                
                $this->evaluatePreconditions($headers);
                
                $bodySize = $body->getSize();
                $bodyLength = $bodySize === null ? null : new ByteCount($bodySize);
                if ($bodyLength === null && ! $isEncoded) {
                    $statistics = $result->lookupFileStatistics();
                    $size = $statistics['size'] ?? null;
                    if ((is_int($size) || is_string($size)) && preg_match('/\A[0-9]+\z/', (string) $size)) {
                        $bodyLength = new ByteCount($size);
                    }
                }
                
                if ($bodyLength === null and $isBufferable) {
                    $body = StreamHelper::cacheStream($body);
                    $bodySize = $body->getSize();
                    $bodyLength = $bodySize === null ? null : new ByteCount($bodySize);
                }
                
                if ($bodyLength === null) {
                    // we don't know the length of the response, so we better figure out a safe transfer coding
                    $transferCoding = $this->negotiateTransferCoding();
                    $body = $transferCoding->encodeStream($body);
                    $transferCodingName = $transferCoding->getHttpName();
                    if ($transferCodingName !== '') {
                        $headers['transfer-encoding'] = $transferCodingName;
                    }
                } else {
                    $headers['accept-ranges'] = 'bytes';
                    if ($request->getMethod() === HTTPRequest::METHOD_GET && ! $bodyLength->isZero() && $request->hasHeader('range') && $this->matchesIfRange($headers)) {
                        $rangeRequest = RangeRequest::parse($request->getHeaderLine('range'));
                        // Multiple ranges are deliberately served as the full representation.
                        if ($rangeRequest !== null && $rangeRequest->getRangeCount() === 1) {
                            $ranges = $rangeRequest->resolve($bodyLength);
                            if ($ranges === []) {
                                $headers['content-range'] = "bytes */$bodyLength";
                                throw new HttpStatusException('', StatusCode::STATUS_REQUESTED_RANGE_NOT_SATISFIABLE, null, $headers);
                            }
                            $range = $ranges[0];
                            $statusCode = StatusCode::STATUS_PARTIAL_CONTENT;
                            $headers['content-range'] = $range->getContentRange();
                            $bodyLength = $range->getLength();
                            $body = new RangeStream($body, $range);
                        }
                    }
                    $headers['content-length'] = (string) $bodyLength;
                }
            } catch (ModuleNotFoundException|AssetPathNotFoundException $e) {
                throw new HttpStatusException($e->getMessage(), StatusCode::STATUS_NOT_FOUND, $e);
            }
        } catch (HttpStatusException $e) {
            $isBufferable = true;
            $statusCode = $e->getCode();
            $headers = $e->getAdditionalHeaders();
            $body = MessageFactory::createStreamFromContents(StatusCode::getMessage($statusCode, $e->getMessage()) . PHP_EOL);
            if ($statusCode === StatusCode::STATUS_REQUESTED_RANGE_NOT_SATISFIABLE) {
                // The error body is plain text, even when the selected representation is encoded.
                unset($headers['content-encoding']);
                $headers['content-type'] = 'text/plain; charset=UTF-8';
                $headers['content-length'] = (string) $body->getSize();
            }
        }
        
        if (! $this->shouldIncludeBody($this->request->getMethod(), $statusCode)) {
            $body = null;
        }
        
        return MessageFactory::createServerResponse($statusCode, $headers, $body, $isBufferable);
    }
    
    /** @param array<string, string> $headers */
    private function evaluatePreconditions(array $headers): void {
        $isRetrieval = in_array($this->request->getMethod(), [HTTPRequest::METHOD_GET, HTTPRequest::METHOD_HEAD], true);
        if ($this->request->hasHeader('if-none-match')) {
            $value = trim($this->request->getHeaderLine('if-none-match'));
            $matches = $value === '*';
            if (isset($headers['etag'])) {
                preg_match_all('/(?:W\/)?"[^"\r\n]*"/', $value, $tags);
                foreach ($tags[0] as $tag) {
                    $matches = $matches || preg_replace('/\AW\//', '', $tag) === $headers['etag'];
                }
            }
            if ($matches) {
                throw new HttpStatusException('', $isRetrieval ? StatusCode::STATUS_NOT_MODIFIED : StatusCode::STATUS_PRECONDITION_FAILED, null, $headers);
            }
        } elseif ($isRetrieval && isset($headers['last-modified']) && $this->request->hasHeader('if-modified-since')) {
            $clientTime = $this->parseHttpDate($this->request->getHeaderLine('if-modified-since'));
            if ($clientTime !== null && $clientTime >= $this->parseHttpDate($headers['last-modified'])) {
                throw new HttpStatusException('', StatusCode::STATUS_NOT_MODIFIED, null, $headers);
            }
        }
    }
    
    /** @param array<string, string> $headers */
    private function matchesIfRange(array $headers): bool {
        if (! $this->request->hasHeader('if-range')) {
            return true;
        }
        $validator = trim($this->request->getHeaderLine('if-range'));
        if (str_starts_with($validator, '"')) {
            return isset($headers['etag']) && $validator === $headers['etag'];
        }
        $time = $this->parseHttpDate($validator);
        return $time !== null && isset($headers['last-modified']) && $time === $this->parseHttpDate($headers['last-modified']);
    }
    
    private function parseHttpDate(string $value): ?int {
        foreach (['!D, d M Y H:i:s \\G\\M\\T', '!l, d-M-y H:i:s \\G\\M\\T', '!D M j H:i:s Y'] as $format) {
            $date = DateTimeImmutable::createFromFormat($format, $value, new DateTimeZone('GMT'));
            $errors = DateTimeImmutable::getLastErrors();
            if ($date !== false && ($errors === false || ($errors['warning_count'] === 0 && $errors['error_count'] === 0))) {
                return $date->getTimestamp();
            }
        }
        return null;
    }
    
    private function validateRequest(): void {
        $clientIp = $this->request->getServerParams()['REMOTE_ADDR'] ?? '';
        if (strlen($clientIp) and BannedManager::getInstance()->isBanned($clientIp)) {
            // BANHAMMER
            throw new HttpStatusException('You have been found wanting.', StatusCode::STATUS_PRECONDITION_FAILED);
        }
        
        $scheme = $this->request->getUri()->getScheme();
        if (! WebScheme::isSupported($scheme) and $scheme !== FarahUrl::SCHEME_DEFAULT) {
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
            if (str_contains($preferred, $name) and str_contains($accept, $name)) {
                return $coding;
            }
        }
        return ContentCoding::identity();
    }
    
    private function negotiateTransferCoding(): CodingInterface {
        $accept = $this->request->getHeaderLine('te');
        if (version_compare($this->request->getProtocolVersion(), '1.1', '>=')) {
            $accept .= ', chunked'; // HTTP 1.1 must accept chunked encoding
        }
        foreach (TransferCoding::getEncodings() as $name => $coding) {
            if ($name === 'chunked' and str_contains($accept, $name)) {
                return $coding;
            }
        }
        return TransferCoding::identity();
    }
    
    private const BODYLESS_METHODS = [
        HTTPRequest::METHOD_HEAD,
        HTTPRequest::METHOD_OPTIONS,
    ];
    
    private const BODYLESS_STATUS_CODES = [
        StatusCode::STATUS_NO_CONTENT,
        StatusCode::STATUS_MULTIPLE_CHOICES,
        StatusCode::STATUS_MOVED_PERMANENTLY,
        StatusCode::STATUS_SEE_OTHER,
        StatusCode::STATUS_NOT_MODIFIED,
        StatusCode::STATUS_TEMPORARY_REDIRECT,
        StatusCode::STATUS_PERMANENT_REDIRECT,
    ];
    
    private function shouldIncludeBody(string $method, int $statusCode): bool {
        return ! in_array($method, self::BODYLESS_METHODS, true)
            && ! in_array($statusCode, self::BODYLESS_STATUS_CODES, true);
    }
    
    private function inventPreferredCompressions(string $mimeType): string {
        return MimeTypeDictionary::guessCompressions($mimeType);
    }
}
