<?php
declare(strict_types = 1);

namespace Slothsoft\Farah;

use DOMDocument;
use DOMNode;
use Exception;
use LogicException;
use RuntimeException;
use Slothsoft\Core\Calendar\Seconds;
use Slothsoft\Core\CascadingDictionary;
use Slothsoft\Core\DOMHelper;
use Slothsoft\Core\FileSystem;
use Slothsoft\Core\IO\FileInfo;
use Slothsoft\Core\IO\HTTPStream;
use Slothsoft\Core\IO\Memory;
use Slothsoft\Core\MimeTypeDictionary;
use Slothsoft\Farah\Exception\ExceptionContext;
use Slothsoft\Farah\Exception\StreamTimedOutException;

/**
 * Legacy HTTP response emitter for headers, body streams, ranges, and content negotiation.
 *
 * @author Daniel Schulz
 * @since 2012-10-19
 * @deprecated Included for historical compatibility only. This API is deprecated and should not be used in new code.
 */
final class HTTPResponse {
    
    public static function cacheDurations(): CascadingDictionary {
        static $field;
        if ($field === null) {
            $field = new CascadingDictionary();
        }
        return $field;
    }
    
    public static function inventCacheDuration(string $mimeType): int {
        return self::cacheDurations()[$mimeType] ?? self::$httpConfig['cache-duration'];
    }
    
    const CHUNK_EOL = "\r\n";
    
    const STATUS_OK = 200;
    
    const STATUS_NO_CONTENT = 204;
    
    const STATUS_PARTIAL_CONTENT = 206;
    
    const STATUS_MULTIPLE_CHOICES = 300;
    
    const STATUS_MOVED_PERMANENTLY = 301;
    
    const STATUS_SEE_OTHER = 303;
    
    const STATUS_NOT_MODIFIED = 304;
    
    const STATUS_TEMPORARY_REDIRECT = 307;
    
    const STATUS_PERMANENT_REDIRECT = 308;
    
    const STATUS_BAD_REQUEST = 400;
    
    const STATUS_UNAUTHORIZED = 401;
    
    const STATUS_NOT_FOUND = 404;
    
    const STATUS_METHOD_NOT_ALLOWED = 405;
    
    const STATUS_GONE = 410;
    
    const STATUS_PRECONDITION_FAILED = 412;
    
    const STATUS_REQUESTED_RANGE_NOT_SATISFIABLE = 416;
    
    const STATUS_INTERNAL_SERVER_ERROR = 500;
    
    const STATUS_NOT_IMPLEMENTED = 501;
    
    const STATUS_HTTP_VERSION_NOT_SUPPORTED = 505;
    
    /** @var array{'date-format': string, 'doc-timestamp': bool, 'cache-duration': int, 'input-size': int, 'download-size': int, 'file-size': int, 'chunk-size': int, 'gzip-level': int} */
    protected static array $httpConfig = [
        'date-format' => 'D, d M Y H:i:s \\G\\M\\T',
        'doc-timestamp' => false, // "rendering took X seconds and Y MB"
        'cache-duration' => 0, // max-age, Sekunden
        'input-size' => Memory::ONE_MEGABYTE, // maximum $env['RESPONSE_INPUT'] size
        'download-size' => 10 * Memory::ONE_MEGABYTE, // if above, force download
        'file-size' => 16 * Memory::ONE_MEGABYTE, // maximum setFile-load
        'chunk-size' => 256 * Memory::ONE_KILOBYTE, // transfer-encoding
        'gzip-level' => 9 // encoding-level
    ];
    
    /** @var array<int, string> */
    protected static array $httpStatusCodes = [
        self::STATUS_OK => 'OK',
        self::STATUS_NO_CONTENT => 'No Content',
        self::STATUS_PARTIAL_CONTENT => 'Partial Content',
        self::STATUS_MULTIPLE_CHOICES => 'Multiple Choices',
        self::STATUS_MOVED_PERMANENTLY => 'Moved Permanently',
        self::STATUS_SEE_OTHER => 'See Other',
        self::STATUS_NOT_MODIFIED => 'Not Modified',
        self::STATUS_TEMPORARY_REDIRECT => 'Temporary Redirect',
        self::STATUS_PERMANENT_REDIRECT => 'Permanent Redirect',
        self::STATUS_BAD_REQUEST => 'Bad Request',
        self::STATUS_UNAUTHORIZED => 'Unauthorized',
        self::STATUS_NOT_FOUND => 'Not Found',
        self::STATUS_METHOD_NOT_ALLOWED => 'Method Not Allowed',
        self::STATUS_GONE => 'Gone',
        self::STATUS_PRECONDITION_FAILED => 'Precondition Failed',
        self::STATUS_REQUESTED_RANGE_NOT_SATISFIABLE => 'Requested Range Not Satisfiable',
        self::STATUS_INTERNAL_SERVER_ERROR => 'Internal Server Error',
        self::STATUS_NOT_IMPLEMENTED => 'Not Implemented',
        self::STATUS_HTTP_VERSION_NOT_SUPPORTED => 'HTTP Version Not Supported'
    ];
    
    const CONTENT_ENCODING_RAW = 'identity';
    
    const CONTENT_ENCODING_GZIP = 'gzip';
    
    /** @var list<string> */
    protected array $supportedContentEncodings = [
        self::CONTENT_ENCODING_RAW,
        self::CONTENT_ENCODING_GZIP
    ];
    
    const TRANSFER_ENCODING_RAW = 'identity';
    
    const TRANSFER_ENCODING_CHUNKED = 'chunked';
    
    /** @var list<string> */
    protected array $supportedTransferEncodings = [
        self::TRANSFER_ENCODING_RAW,
        self::TRANSFER_ENCODING_CHUNKED
    ];
    
    const BODY_STRING = 1;
    
    // body = output string
    const BODY_FILE = 2;
    
    // body = path to file
    const BODY_STREAM = 3;
    
    // body = HTTPStream
    const BODY_COMMAND = 4;
    
    // body = HTTPCommand
    
    /** @param array<array-key, mixed> $config */
    public static function setHttpConfig(array $config): void {
        foreach (self::$httpConfig as $key => &$val) {
            if (isset($config[$key])) {
                settype($config[$key], gettype($val));
                $val = $config[$key];
            }
        }
        unset($val);
    }
    
    public static function calcEtag(mixed $data): string {
        return md5((string) $data);
    }
    
    /** @var array<array-key, mixed> */
    protected array $headerList = [];
    
    protected string|HTTPStream|HTTPCommand|null $body = null;
    
    protected int $bodyType;
    
    protected ?int $bodyLength = null;
    
    protected ?int $rangeStart = null;
    
    protected ?int $rangeEnd = null;
    
    protected mixed $method = null;
    
    protected mixed $status = null;
    
    protected string $charset;
    
    protected string $contentEncoding;
    
    /** @var list<string> */
    protected array $contentEncodingList = [];
    
    protected string $transferEncoding;
    
    /** @var list<string> */
    protected array $transferEncodingList = [];
    
    protected string $mime;
    
    protected mixed $language = null;
    
    protected mixed $fileName = null;
    
    protected mixed $fileExt = null;
    
    protected string $fileDisposition;
    
    protected mixed $ifNoneMatch = null;
    
    protected int|false|null $ifModifiedSince = null;
    
    /** @var array<int, mixed> */
    protected array $supportedNegotiations = [];
    
    protected mixed $protocolName = null;
    
    protected mixed $protocolMajorVersion = null;
    
    protected mixed $protocolMinorVersion = null;
    
    public mixed $includeBody = true;
    
    public function __construct() {
        $this->charset = 'UTF-8';
        $this->mime = 'text/plain';
        $this->contentEncoding = self::CONTENT_ENCODING_RAW;
        $this->transferEncoding = self::TRANSFER_ENCODING_RAW;
        $this->bodyType = self::BODY_STRING;
        $this->status = self::STATUS_INTERNAL_SERVER_ERROR;
        $this->fileName = 'index';
        $this->fileExt = 'txt';
        $this->fileDisposition = 'inline';
        
        $this->method = HTTPRequest::METHOD_GET;
        $this->protocolName = HTTPRequest::PROTOCOL_HTTP;
        $this->protocolMajorVersion = 1;
        $this->protocolMinorVersion = 1;
    }
    
    public function setRequest(HTTPRequest $httpRequest): void {
        $this->setMethod($httpRequest->method);
        $this->setContentEncoding($httpRequest->getHeader('accept-encoding', ''));
        $this->setRange($httpRequest->getHeader('range', ''));
        $this->ifNoneMatch = $httpRequest->getHeader('if-none-match', '');
        $this->ifModifiedSince = strtotime($httpRequest->getHeader('if-modified-since', ''));
        $this->supportedNegotiations = [
            'accept-encoding'
        ];
        $this->protocolName = $httpRequest->protocolName;
        $this->protocolMajorVersion = $httpRequest->protocolMajorVersion;
        $this->protocolMinorVersion = $httpRequest->protocolMinorVersion;
    }
    
    /** @param array<array-key, mixed> $env */
    public function addTrackingInfo(array &$env): void {
        $env['RESPONSE_TIME'] = get_execution_time();
        $env['RESPONSE_MEMORY'] = sprintf('%.2f', memory_get_peak_usage() / 1048576);
        $env['RESPONSE_STATUS'] = $this->status;
        if ($this->status === self::STATUS_OK and $this->rangeStart !== $this->rangeEnd) {
            $env['RESPONSE_STATUS'] = self::STATUS_PARTIAL_CONTENT;
        }
        $env['RESPONSE_TYPE'] = $this->mime;
        $env['RESPONSE_ENCODING'] = $this->contentEncoding;
        $env['RESPONSE_LENGTH'] = $this->bodyLength;
        $env['RESPONSE_LANGUAGE'] = $this->language;
        try {
            $input = FileInfo::readContents('php://input');
        } catch (RuntimeException) {
            $input = '';
        }
        if (strlen($input) > 0 and strlen($input) < self::$httpConfig['input-size']) {
            $env['RESPONSE_INPUT'] = $input;
        }
    }
    
    public function send(): void {
        if (! headers_sent() and ! connection_aborted()) {
            if ($this->bodyHasChanged() === false) {
                $this->setStatus(self::STATUS_NOT_MODIFIED);
            }
            
            if ($this->status === self::STATUS_OK && $this->bodyLength !== null && ($this->rangeStart !== null || $this->rangeEnd !== null)) {
                $this->applyRange($this->bodyLength);
            }
            if ($this->rangeStart === null) {
                $this->rangeStart = 0;
            }
            if ($this->rangeEnd === null) {
                $this->rangeEnd = $this->bodyLength;
            }
            $this->sendHeaderList();
            
            switch ($this->method) {
                case HTTPRequest::METHOD_OPTIONS:
                case HTTPRequest::METHOD_HEAD:
                    break;
                default:
                    $this->sendBody();
                    break;
            }
        }
    }
    
    private function applyRange(int $size): void {
        $start = $this->rangeStart ?? 0;
        $end = min($this->rangeEnd ?? $size, $size);
        if ($start >= $end) {
            $this->setStatus(self::STATUS_REQUESTED_RANGE_NOT_SATISFIABLE);
            $this->addHeader('content-range', 'bytes */%d', [$size]);
            $this->rangeStart = null;
            $this->rangeEnd = null;
            return;
        }
        $this->rangeStart = $start;
        $this->rangeEnd = $end;
        $this->setStatus(self::STATUS_PARTIAL_CONTENT);
    }
    
    public function addHeader(mixed $key, mixed $val, mixed $param = null): void {
        $key = strtolower(trim($key));
        $this->headerList[$key] = $param === null ? $val : vsprintf($val, $param);
    }
    
    public function removeHeader(mixed $key): void {
        $key = strtolower(trim($key));
        unset($this->headerList[$key]);
    }
    
    public function getHeader(mixed $key): mixed {
        $key = strtolower($key);
        return $this->headerList[$key] ?? null;
    }
    
    public function addNegotiation(mixed $negotiation): void {
        if (! in_array($negotiation, $this->supportedNegotiations)) {
            $this->supportedNegotiations[] = $negotiation;
        }
    }
    
    public function removeNegotiation(mixed $negotiation): bool {
        foreach ($this->supportedNegotiations as $i => $tmp) {
            if ($tmp === $negotiation) {
                unset($this->supportedNegotiations[$i]);
                return true;
            }
        }
        return false;
    }
    
    public function setLanguage(mixed $language): void {
        $this->language = $language;
        if ($language) {
            $this->addHeader('content-language', $language);
            $this->addNegotiation('accept-language');
        } else {
            $this->removeHeader('content-language');
            $this->removeNegotiation('accept-language');
        }
    }
    
    public function setFileName(mixed $fileName): void {
        $this->fileName = $fileName;
    }
    
    public function setFileExt(mixed $fileExt, mixed $guessMime = false): void {
        $this->fileExt = $fileExt;
        if ($guessMime) {
            $this->mime = MimeTypeDictionary::guessMime($this->fileExt);
        }
    }
    
    public function setMethod(mixed $method): void {
        $this->method = $method;
    }
    
    public function setStatus(mixed $code, mixed $message = ''): void {
        if (isset(self::$httpStatusCodes[$code])) {
            $this->status = $code;
            if ($code >= self::STATUS_MULTIPLE_CHOICES or $code === self::STATUS_NO_CONTENT) {
                $this->includeBody = false;
            }
            if ($code >= self::STATUS_BAD_REQUEST) {
                $this->includeBody = true;
                $this->setBody(sprintf('%d %s', $this->status, self::$httpStatusCodes[$this->status]) . PHP_EOL . $message);
            }
            if ($code === self::STATUS_REQUESTED_RANGE_NOT_SATISFIABLE) {
                $this->includeBody = false;
            }
        }
    }
    
    public function getStatus(): int {
        return $this->status;
    }
    
    public function setRange(string $range): void {
        $match = [];
        if (preg_match('/^bytes=(\d*)-(\d*)(.*)$/', $range, $match)) {
            if ($match[3]) {
                $this->setStatus(self::STATUS_REQUESTED_RANGE_NOT_SATISFIABLE);
            } else {
                // Digit strings beyond the native range cast to PHP_INT_MAX.
                $this->rangeStart = $match[1] === '' ? null : (int) $match[1];
                $this->rangeEnd = $match[2] === '' ? null : min((int) $match[2], PHP_INT_MAX - 1) + 1;
            }
        }
    }
    
    public function setDownload(mixed $isDownload): void {
        $this->fileDisposition = $isDownload ? 'attachment' : 'inline';
    }
    
    public function setRedirect(mixed $uri, mixed $permanent = false, mixed $forceGET = false): void {
        $this->setStatus($forceGET ? self::STATUS_SEE_OTHER : ($permanent ? self::STATUS_PERMANENT_REDIRECT : self::STATUS_TEMPORARY_REDIRECT));
        $this->addHeader('location', $uri);
        // $this->setBody($uri);
    }
    
    public function setMoved(mixed $uri, mixed $permanent = false): void {
        $this->setStatus($permanent ? self::STATUS_MOVED_PERMANENTLY : self::STATUS_TEMPORARY_REDIRECT);
        $this->addHeader('location', $uri);
        $this->setBody($uri);
    }
    
    public function setFile(mixed $filePath, mixed $fileName = null): void {
        if ($fileName === null) {
            $fileName = basename($filePath);
        }
        if ($fileName) {
            if ($pos = strrpos($fileName, '.')) {
                $this->setFileName(substr($fileName, 0, $pos));
                $this->setFileExt(substr($fileName, $pos + 1), true);
            } else {
                $this->setFileName($fileName);
                $this->setFileExt('');
            }
        }
        if ($filePath = (string) $filePath) {
            $this->setStatus(self::STATUS_OK);
            
            $size = FileSystem::size($filePath);
            if ($size === null) {
                throw new RuntimeException(sprintf('Failed to determine the size of asset file "%s".', $filePath));
            }
            $changetime = FileSystem::changetime($filePath);
            if ($size > self::$httpConfig['download-size']) {
                $this->setDownload(true);
            }
            if ($size < self::$httpConfig['file-size']) {
                $this->setBody(FileInfo::readContents($filePath));
                $this->setEtag(self::calcEtag($this->body));
            } else {
                $this->body = $filePath;
                $this->bodyLength = $size;
                $this->bodyType = self::BODY_FILE;
                $this->contentEncoding = self::CONTENT_ENCODING_RAW;
                $this->setEtag(self::calcEtag($changetime));
            }
            $this->setLastModified($changetime);
        }
        // $this->addHeader('content-type', 'application/octet-stream');
    }
    
    public function setStream(HTTPStream $stream): void {
        $this->setStatus(self::STATUS_OK);
        $this->body = $stream;
        $this->rangeStart = null;
        $this->rangeEnd = null;
        $this->bodyLength = null;
        $this->bodyType = self::BODY_STREAM;
        $this->contentEncoding = self::CONTENT_ENCODING_RAW;
        switch ($this->protocolName) {
            case HTTPRequest::PROTOCOL_HTTP:
                if ($this->protocolMajorVersion >= 1 and $this->protocolMinorVersion >= 1) {
                    $this->transferEncoding = self::TRANSFER_ENCODING_CHUNKED;
                }
                break;
            default:
                break;
        }
        
        $headerList = $stream->getHeaderList();
        foreach ($headerList as $key => $val) {
            $this->addHeader($key, $val);
        }
        if ($mime = $stream->getMime()) {
            $this->mime = $mime;
        }
        if ($charset = $stream->getEncoding()) {
            $this->charset = $charset;
        }
    }
    
    public function setCommand(HTTPCommand $command): void {
        $this->setStatus(self::STATUS_OK);
        $this->body = $command;
        $this->rangeStart = null;
        $this->rangeEnd = null;
        $this->bodyLength = null;
        $this->bodyType = self::BODY_COMMAND;
        $this->contentEncoding = self::CONTENT_ENCODING_RAW;
        
        $headerList = $command->getHeaderList();
        foreach ($headerList as $key => $val) {
            $this->addHeader($key, $val);
        }
    }
    
    public function getDocument(): DOMDocument {
        if (! is_string($this->body)) {
            throw new LogicException('A string response body is required to read an XML document.');
        }
        $retDoc = new DOMDocument();
        $retDoc->loadXML($this->body);
        return $retDoc;
    }
    
    public function getDocumentElement(DOMDocument $targetDoc): ?DOMNode {
        $doc = $this->getDocument();
        return $doc->documentElement ? $targetDoc->importNode($doc->documentElement, true) : null;
    }
    
    public function setExceptionContext(ExceptionContext $exception): void {
        $this->setDocument($exception->toDocument());
        $this->status = self::STATUS_INTERNAL_SERVER_ERROR;
    }
    
    private const ERR_REQRES = 'Rendering of this page took %d ms and %.2f MB.';
    
    public function setDocument(DOMDocument $doc): void {
        if ($doc->documentURI and $fileName = basename($doc->documentURI)) {
            $this->setFile(null, $fileName);
        }
        if ($doc->documentElement) {
            if ($doc->documentElement->hasAttribute('xml:lang')) {
                $this->setLanguage($doc->documentElement->getAttribute('xml:lang'));
            }
        }
        if ($this->charset) {
            $doc->encoding = $this->charset;
        }
        
        $ns = $doc->documentElement?->namespaceURI;
        $doctype = null;
        switch ($ns) {
            case DOMHelper::NS_HTML:
                $this->mime = 'application/xhtml+xml';
                $doctype = $doc->doctype ?? $doc->implementation->createDocumentType('html');
                break;
            case DOMHelper::NS_SVG:
                $this->mime = 'image/svg+xml';
                break;
            case DOMHelper::NS_XSL:
                $this->mime = 'application/xslt+xml';
                break;
            default:
                $this->mime = 'application/xml';
                break;
        }
        if ($ext = MimeTypeDictionary::guessExtension($this->mime)) {
            $this->setFileExt($ext);
        }
        $this->setStatus(self::STATUS_OK);
        
        if ($doctype) {
            $doc->insertBefore($doctype, $doc->documentElement);
        }
        
        if (self::$httpConfig['doc-timestamp']) {
            printf(self::ERR_REQRES, get_execution_time(), memory_get_peak_usage() / 1048576);
            $this->setEtag(self::calcEtag(self::serializeDocument($doc)), false);
            $root = $doc->documentElement;
            if ($root !== null) {
                $root->insertBefore($doc->createComment(PHP_EOL . sprintf(self::ERR_REQRES, get_execution_time(), memory_get_peak_usage() / 1048576) . PHP_EOL), $root->firstChild);
            }
            $this->setBody(trim(self::serializeDocument($doc)));
        } else {
            $this->setBody(trim(self::serializeDocument($doc)));
            $this->setEtag(self::calcEtag($this->body));
        }
    }
    
    private static function serializeDocument(DOMDocument $document): string {
        $xml = $document->saveXML();
        if ($xml === false) {
            throw new RuntimeException('Failed to serialize the XML response.');
        }
        return $xml;
    }
    
    public function setBody(mixed $data): void {
        $data = (string) $data;
        if (in_array(self::CONTENT_ENCODING_GZIP, $this->contentEncodingList)) {
            $data = gzencode($data, self::$httpConfig['gzip-level']);
            if ($data === false) {
                throw new RuntimeException('Failed to gzip the response body.');
            }
            $this->contentEncoding = self::CONTENT_ENCODING_GZIP;
        } else {
            $this->contentEncoding = self::CONTENT_ENCODING_RAW;
        }
        $this->body = $data;
        $this->bodyLength = strlen($data);
        $this->bodyType = self::BODY_STRING;
    }
    
    public function getBody(): mixed {
        return $this->body;
    }
    
    protected function bodyHasChanged(): bool {
        $ret = true;
        $date = $this->getHeader('last-modified');
        if ($date and $this->ifModifiedSince) {
            $time = strtotime($date);
            $ret = ($time <= $this->ifModifiedSince);
        }
        $etag = $this->getHeader('etag');
        if ($etag and $this->ifNoneMatch) {
            $ret = ($etag !== $this->ifNoneMatch);
        }
        return $ret;
    }
    
    public function setEtag(mixed $etag, mixed $isStrong = true): void { // https://tools.ietf.org/html/rfc7232#section-2.3
        $etag = sprintf('%s"%s"', $isStrong ? '' : 'W/', $etag);
        $this->addHeader('etag', $etag);
    }
    
    public function setLastModified(mixed $time = null, mixed $isStrong = true): void {
        $date = gmdate(self::$httpConfig['date-format'], $time === null ? time() : $time);
        $this->addHeader('last-modified', $date);
    }
    
    public function setContentEncoding(string $contentEncoding): void {
        if (strlen($contentEncoding)) {
            $contentEncodingList = explode(',', $contentEncoding);
            foreach ($contentEncodingList as $contentEncoding) {
                $contentEncoding = trim($contentEncoding);
                if (in_array($contentEncoding, $this->supportedContentEncodings)) {
                    $this->contentEncodingList[] = $contentEncoding;
                }
            }
        }
    }
    
    protected function sendHeaderList(): void {
        // $this->addHeader('connection', 'Keep-Alive');
        if ($this->rangeEnd !== null) {
            $this->addHeader('accept-ranges', 'bytes');
        }
        if ($this->includeBody or $this->method === HTTPRequest::METHOD_HEAD) {
            $this->addHeader('content-type', '%s; charset=%s', [
                $this->mime,
                $this->charset
            ]);
            // $this->addHeader('content-disposition', '%s; filename="%s.%s"', [$this->fileDisposition, $this->fileName, $this->fileExt]);
            $file = sprintf('%s.%s', $this->fileName, $this->fileExt);
            $this->addHeader('content-disposition', '%s; filename="%s"; filename*=UTF-8\'\'%s', [
                $this->fileDisposition,
                preg_replace('/[^[:print:]]/', '', $file),
                rawurlencode($file)
            ]);
            if ($this->rangeEnd !== null) {
                if ($this->transferEncoding === self::TRANSFER_ENCODING_RAW) {
                    $this->addHeader('content-length', $this->rangeEnd - $this->rangeStart);
                }
                if ($this->status === self::STATUS_PARTIAL_CONTENT) {
                    $this->addHeader('content-range', 'bytes %d-%d/%d', [
                        $this->rangeStart,
                        $this->rangeEnd - 1,
                        $this->bodyLength
                    ]);
                }
            }
            if ($this->contentEncoding and $this->contentEncoding !== self::CONTENT_ENCODING_RAW) {
                $this->addHeader('content-encoding', $this->contentEncoding);
            }
            if ($this->transferEncoding and $this->transferEncoding !== self::TRANSFER_ENCODING_RAW) {
                $this->addHeader('transfer-encoding', $this->transferEncoding);
            }
        }
        switch ($this->bodyType) {
            case self::BODY_STRING:
            case self::BODY_FILE:
                $cacheDuration = self::inventCacheDuration($this->mime);
                $this->addHeader('cache-control', 'must-revalidate, max-age=%d', [
                    $cacheDuration
                ]);
                break;
            case self::BODY_STREAM:
            case self::BODY_COMMAND:
                $this->addHeader('cache-control', 'private, no-cache');
                break;
        }
        if ($this->supportedNegotiations !== []) {
            $this->addHeader('vary', implode(', ', $this->supportedNegotiations));
        }
        header(sprintf('%s/%d.%d %d %s', $this->protocolName, $this->protocolMajorVersion, $this->protocolMinorVersion, $this->status, self::$httpStatusCodes[$this->status]), true, $this->status);
        foreach ($this->headerList as $key => $val) {
            header(sprintf('%s:%s', $key, $val));
        }
        flush();
        $this->headerList = [];
    }
    
    protected function sendBody(): void {
        if ($this->includeBody) {
            set_time_limit(Seconds::DAY);
            switch ($this->bodyType) {
                case self::BODY_STRING:
                    if (! is_string($this->body)) {
                        throw new LogicException('The string response body is missing.');
                    }
                    $start = $this->rangeStart ?? 0;
                    $end = $this->rangeEnd ?? $start;
                    if ($start === 0 && $end === $this->bodyLength) {
                        $this->sendBodyChunk($this->body);
                    } else {
                        $this->sendBodyChunk(substr($this->body, $start, $end - $start));
                    }
                    break;
                case self::BODY_FILE:
                    $path = $this->body;
                    if (! is_string($path)) {
                        throw new LogicException('The file response path is missing.');
                    }
                    $start = $this->rangeStart ?? 0;
                    $length = ($this->rangeEnd ?? $start) - $start;
                    
                    $handle = @fopen($path, 'rb');
                    if ($handle === false) {
                        throw new RuntimeException(sprintf('Failed to open asset file "%s".', $path));
                    }
                    try {
                        if (fseek($handle, $start) !== 0) {
                            throw new RuntimeException(sprintf('Failed to seek to byte %d of "%s".', $start, $path));
                        }
                        $chunkSize = max(1, self::$httpConfig['file-size']);
                        while ($length > 0) {
                            $chunk = fread($handle, min($chunkSize, $length));
                            if ($chunk === false || $chunk === '') {
                                throw new RuntimeException(sprintf('Failed to read the requested bytes from "%s".', $path));
                            }
                            $this->sendBodyChunk($chunk);
                            $length -= strlen($chunk);
                        }
                    } finally {
                        fclose($handle);
                    }
                    break;
                case self::BODY_STREAM:
                    $stream = $this->body;
                    if (! $stream instanceof HTTPStream) {
                        throw new LogicException('The stream response body is missing.');
                    }
                    $intervalTime = 0;
                    $timeoutTime = 0;
                    $sleepDuration = $stream->getSleepDuration();
                    $heartbeatContent = $stream->getHeartbeatContent();
                    $heartbeatInterval = $stream->getHeartbeatInterval();
                    $heartbeatTimeout = $stream->getHeartbeatTimeout();
                    $heartbeatEOL = $stream->getHeartbeatEOL();
                    $heartbeatSent = false;
                    while (! connection_aborted()) {
                        try {
                            $status = $stream->getStatus();
                            if ($timeoutTime > $heartbeatTimeout) {
                                throw new StreamTimedOutException($stream::class);
                            }
                        } catch (Exception) {
                            $status = HTTPStream::STATUS_ERROR;
                        }
                        switch ($status) {
                            case HTTPStream::STATUS_CONTENTDONE:
                                $content = $stream->getContent();
                                if ($content !== '') {
                                    if ($heartbeatSent and $heartbeatEOL !== null) {
                                        $this->sendBodyChunk($heartbeatEOL);
                                    }
                                    $this->sendBodyChunk($content);
                                }
                            // no break
                            case HTTPStream::STATUS_DONE:
                            case HTTPStream::STATUS_ERROR:
                                break 2;
                            case HTTPStream::STATUS_CONTENT:
                                $content = $stream->getContent();
                                if ($content !== '') {
                                    if ($heartbeatSent and $heartbeatEOL !== null) {
                                        $this->sendBodyChunk($heartbeatEOL);
                                        $heartbeatSent = false;
                                    }
                                    $intervalTime = 0;
                                    $timeoutTime = 0;
                                    $this->sendBodyChunk($content);
                                }
                                break;
                            case HTTPStream::STATUS_RETRY:
                                $intervalTime += $sleepDuration;
                                $timeoutTime += $sleepDuration;
                                usleep((int) ($sleepDuration * Seconds::USLEEP_FACTOR));
                                if ($intervalTime > $heartbeatInterval and $heartbeatContent !== null) {
                                    $intervalTime = 0;
                                    $this->sendBodyChunk($heartbeatContent);
                                    $heartbeatSent = true;
                                }
                                break;
                        }
                    }
                    break;
                case self::BODY_COMMAND:
                    $command = $this->body;
                    if (! $command instanceof HTTPCommand) {
                        throw new LogicException('The command response body is missing.');
                    }
                    $command->addEventListener('output', function (HTTPEvent $eve) {
                        $this->sendBodyChunk($eve->data);
                    });
                    $command->run();
                    break;
            }
            $this->sendBodyEnd();
        }
    }
    
    protected function sendBodyChunk(mixed $chunk): void {
        switch ($this->transferEncoding) {
            case self::TRANSFER_ENCODING_RAW:
                // file_put_contents('php://output', $chunk, FILE_APPEND);
                echo $chunk;
                break;
            case self::TRANSFER_ENCODING_CHUNKED:
                for ($i = 0, $j = strlen($chunk); $i < $j; $i += self::$httpConfig['chunk-size']) {
                    $tmp = substr($chunk, $i, self::$httpConfig['chunk-size']);
                    echo dechex(strlen($tmp));
                    echo self::CHUNK_EOL;
                    echo $tmp;
                    echo self::CHUNK_EOL;
                }
                break;
        }
    }
    
    protected function sendBodyEnd(): void {
        switch ($this->transferEncoding) {
            case self::TRANSFER_ENCODING_RAW:
                break;
            case self::TRANSFER_ENCODING_CHUNKED:
                echo '0' . self::CHUNK_EOL . self::CHUNK_EOL;
                break;
        }
    }
}
