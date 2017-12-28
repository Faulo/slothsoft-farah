<?php
/***********************************************************************
 * Slothsoft\Farah\HTTPResponse v1.00 19.10.2012 © Daniel Schulz
 * 
 * 	Changelog:
 *		v1.00 19.10.2012
 *			initial release
 ***********************************************************************/
namespace Slothsoft\Farah;

use Slothsoft\Core\FileSystem;
use MatthiasMullie\Minify\CSS;
use MatthiasMullie\Minify\JS;
use DOMDocument;
use Exception;
use UnexpectedValueException;
declare(ticks = 1000);

class HTTPResponse
{

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

    protected static $httpConfig = [
        'date-format' => 'D, d M Y H:i:s \\G\\M\\T',
        'doc-timestamp' => false, // "rendering took X seconds and Y MB"
        'merge-styleFiles' => CMS_RESPONSE_MERGE_STYLEFILES,
        'merge-scriptFiles' => CMS_RESPONSE_MERGE_SCRIPTFILES,
        'minify-styleFiles' => CMS_RESPONSE_MINIFY_STYLEFILES,
        'minify-scriptFiles' => CMS_RESPONSE_MINIFY_SCRIPTFILES,
        'cache-duration' => 30, // max-age, Sekunden
        'file-size' => 16 * MEMORY_MEGABYTE, // maximum setFile-load
        'seek-size' => 1 * MEMORY_MEGABYTE, // maximum fseek
        'chunk-size' => 256 * MEMORY_KILOBYTE, // transfer-encoding
        'gzip-level' => 9 // encoding-level
    ];

    protected static $httpStatusCodes = [
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

    protected $supportedContentEncodings = [
        self::CONTENT_ENCODING_RAW,
        self::CONTENT_ENCODING_GZIP
    ];

    const TRANSFER_ENCODING_RAW = 'identity';

    const TRANSFER_ENCODING_CHUNKED = 'chunked';

    protected $supportedTransferEncodings = [
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
    public static function setHttpConfig(array $config)
    {
        foreach (self::$httpConfig as $key => &$val) {
            if (isset($config[$key])) {
                settype($config[$key], gettype($val));
                $val = $config[$key];
            }
        }
        unset($val);
    }

    public static function calcEtag($data)
    {
        return md5($data);
    }

    public $styleFiles = [];

    public $scriptFiles = [];

    protected $headerList;

    protected $body;

    protected $bodyType;

    protected $bodyLength;

    protected $rangeStart;

    protected $rangeEnd;

    protected $method;

    protected $status;

    protected $charset;

    protected $contentEncoding;

    protected $contentEncodingList;

    protected $transferEncoding;

    protected $transferEncodingList;

    protected $mime;

    protected $language;

    protected $doctype;

    protected $fileName;

    protected $fileExt;

    protected $fileDisposition;

    protected $cache;

    protected $ifNoneMatch;

    protected $ifModifiedSince;

    protected $supportedNegotiations;

    protected $protocolName;

    protected $protocolMajorVersion;

    protected $protocolMinorVersion;

    public $includeBody = true;

    public $includeStyle = true;

    public $includeScript = true;

    public function __construct()
    {
        $this->charset = 'UTF-8';
        $this->mime = 'text/plain';
        $this->language = null;
        $this->contentEncoding = self::CONTENT_ENCODING_RAW;
        $this->contentEncodingList = [];
        $this->transferEncoding = self::TRANSFER_ENCODING_RAW;
        $this->transferEncodingList = [];
        $this->bodyType = self::BODY_STRING;
        $this->rangeStart = null;
        $this->rangeEnd = null;
        $this->status = self::STATUS_INTERNAL_SERVER_ERROR;
        $this->fileName = 'index';
        $this->fileExt = 'txt';
        $this->fileDisposition = 'inline';
        $this->doctype = null;
        $this->ifNoneMatch = null;
        
        $this->method = HTTPRequest::METHOD_GET;
        $this->protocolName = HTTPRequest::PROTOCOL_HTTP;
        $this->protocolMajorVersion = 1;
        $this->protocolMinorVersion = 1;
        
        $this->cache = new Cache();
        $this->headerList = [];
    }

    public function setRequest(HTTPRequest $httpRequest)
    {
        $this->setMethod($httpRequest->method);
        $this->setContentEncoding($httpRequest->getHeader('accept-encoding'));
        $this->setRange($httpRequest->getHeader('range'));
        $this->ifNoneMatch = $httpRequest->getHeader('if-none-match');
        $this->ifModifiedSince = strtotime($httpRequest->getHeader('if-modified-since'));
        $this->supportedNegotiations = [
            'accept-encoding'
        ];
        $this->protocolName = $httpRequest->protocolName;
        $this->protocolMajorVersion = $httpRequest->protocolMajorVersion;
        $this->protocolMinorVersion = $httpRequest->protocolMinorVersion;
    }

    public function addTrackingInfo(array &$env)
    {
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
        $input = file_get_contents('php://input');
        if (strlen($input) > 0 and strlen($input) < self::$httpConfig['file-size']) {
            $env['RESPONSE_INPUT'] = $input;
        }
    }

    public function send()
    {
        if (! headers_sent() and ! connection_aborted()) {
            if ($this->bodyHasChanged() === false) {
                $this->setStatus(self::STATUS_NOT_MODIFIED);
            }
            
            if ($this->status === self::STATUS_OK and $this->rangeStart !== $this->rangeEnd) {
                $this->setStatus(self::STATUS_PARTIAL_CONTENT);
            }
            if ($this->rangeStart === null) {
                $this->rangeStart = 0;
            }
            if ($this->rangeEnd === null) {
                $this->rangeEnd = $this->bodyLength;
            }
            $this->sendHeaderList();
            
            switch ($this->method) {
                case HTTPRequest::METHOD_HEAD:
                    break;
                default:
                    $this->sendBody();
                    break;
            }
        }
    }

    public function addHeader($key, $val, $param = null)
    {
        $key = strtolower(trim($key));
        $this->headerList[$key] = $param === null ? $val : vsprintf($val, $param);
    }

    public function removeHeader($key)
    {
        $key = strtolower(trim($key));
        unset($this->headerList[$key]);
    }

    public function getHeader($key)
    {
        $key = strtolower($key);
        return isset($this->headerList[$key]) ? $this->headerList[$key] : null;
    }

    public function addNegotiation($negotiation)
    {
        if (! in_array($negotiation, $this->supportedNegotiations)) {
            $this->supportedNegotiations[] = $negotiation;
        }
    }

    public function removeNegotiation($negotiation)
    {
        foreach ($this->supportedNegotiations as $i => $tmp) {
            if ($tmp === $negotiation) {
                unset($this->supportedNegotiations[$i]);
                return true;
            }
        }
        return false;
    }

    public function setLanguage($language)
    {
        $this->language = $language;
        if ($language) {
            $this->addHeader('content-language', $language);
            $this->addNegotiation('accept-language');
        } else {
            $this->removeHeader('content-language');
            $this->removeNegotiation('accept-language');
        }
    }

    public function setFileName($fileName)
    {
        $this->fileName = $fileName;
    }

    public function setFileExt($fileExt, $guessMime = false)
    {
        $this->fileExt = $fileExt;
        if ($guessMime) {
            $this->mime = Resource::getMime($this->fileExt);
        }
    }

    public function setMethod($method)
    {
        $this->method = $method;
    }

    public function setStatus($code, $message = '')
    {
        if (isset(self::$httpStatusCodes[$code])) {
            $this->status = $code;
            if ($code >= self::STATUS_MULTIPLE_CHOICES or $code === self::STATUS_NO_CONTENT or $code === self::STATUS_NOT_MODIFIED) {
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
	public function getStatus() {
		return $this->status;
	}

    public function setRange($range)
    {
        if (preg_match('/^bytes=(\d*)-(\d*)(.*)$/', $range, $match)) {
            if ($match[3]) {
                $this->setStatus(self::STATUS_REQUESTED_RANGE_NOT_SATISFIABLE);
            } else {
                $this->rangeStart = strlen($match[1]) ? (float) $match[1] : null;
                $this->rangeEnd = strlen($match[2]) ? (float) $match[2] + 1 : null;
            }
        }
    }

    public function setDownload($isDownload)
    {
        $this->fileDisposition = $isDownload ? 'attachment' : 'inline';
    }

    public function setRedirect($uri, $permanent = false, $forceGET = false)
    {
        $this->setStatus($forceGET ? self::STATUS_SEE_OTHER : ($permanent ? self::STATUS_PERMANENT_REDIRECT : self::STATUS_TEMPORARY_REDIRECT));
        $this->addHeader('location', $uri);
        // $this->setBody($uri);
    }

    public function setMoved($uri, $permanent = false)
    {
        $this->setStatus($permanent ? self::STATUS_MOVED_PERMANENTLY : self::STATUS_TEMPORARY_REDIRECT);
        $this->addHeader('location', $uri);
        $this->setBody($uri);
    }

    public function setFile($filePath = null, $fileName = null)
    {
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
            $changetime = FileSystem::changetime($filePath);
            if ($size < self::$httpConfig['file-size']) {
                $this->setBody(file_get_contents($filePath));
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

    public function setStream(HTTPStream $stream)
    {
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
            default:
                break;
        }
        
        $headerList = $this->body->getHeaderList();
        foreach ($headerList as $key => $val) {
            $this->addHeader($key, $val);
        }
        if ($mime = $this->body->getMime()) {
            $this->mime = $mime;
        }
        if ($charset = $this->body->getEncoding()) {
            $this->charset = $charset;
        }
    }

    public function setCommand(HTTPCommand $command)
    {
        $this->setStatus(self::STATUS_OK);
        $this->body = $command;
        $this->rangeStart = null;
        $this->rangeEnd = null;
        $this->bodyLength = null;
        $this->bodyType = self::BODY_COMMAND;
        $this->contentEncoding = self::CONTENT_ENCODING_RAW;
        
        $headerList = $this->body->getHeaderList();
        foreach ($headerList as $key => $val) {
            $this->addHeader($key, $val);
        }
        if ($mime = $this->body->getMime()) {
            $this->mime = $mime;
        }
        if ($charset = $this->body->getEncoding()) {
            $this->charset = $charset;
        }
    }

    public function getDocument()
    {
        $retDoc = new DOMDocument();
        $retDoc->loadXML($this->body);
        return $retDoc;
    }

    public function getDocumentElement(DOMDocument $targetDoc)
    {
        $ret = null;
        if ($doc = $this->getDocument()) {
            if ($doc->documentElement) {
                if ($node = $targetDoc->importNode($doc->documentElement, true)) {
                    $ret = $node;
                }
            }
        }
        return $ret;
    }

    public function getCacheManifestFile()
    {
        return HTTPFile::createFromString($this->getCacheManifestContent(), 'manifest.appcache');
    }

    public function getCacheManifestContent()
    {
        $manifest = <<<'EOT'
CACHE MANIFEST

CACHE:
%s
%s

NETWORK:
*

EOT;
        return sprintf($manifest, $this->getMergedStyleFile($this->styleFiles), $this->getMergedScriptFile($this->scriptFiles));
    }

    protected function getMergedStyleFile(array $styleFiles)
    {
        if (self::$httpConfig['minify-styleFiles']) {
            return $this->cache->mergeFiles($styleFiles, 'css/', function ($buffer) {
                $minifier = new CSS($buffer);
                return $minifier->minify();
            });
        } else {
            return $this->cache->mergeFiles($styleFiles, 'css/');
        }
    }

    protected function getMergedScriptFile(array $scriptFiles)
    {
        if (self::$httpConfig['minify-scriptFiles']) {
            return $this->cache->mergeFiles($scriptFiles, 'js/', function ($buffer) {
                $minifier = new JS($buffer);
                return $minifier->minify();
            });
        } else {
            return $this->cache->mergeFiles($scriptFiles, 'js/');
        }
    }

    public function setDocument(DOMDocument $doc)
    {
        if ($doc->documentURI and $fileName = basename($doc->documentURI)) {
            $this->setFile(null, $fileName);
        }
        if ($doc->documentElement) {
            if ($doc->documentElement->hasAttribute('xml:lang')) {
                $this->setLanguage($doc->documentElement->getAttribute('xml:lang'));
            } elseif ($this->language) {
                //$doc->documentElement->setAttribute('xml:lang', $this->language);
            }
        }
        if ($this->charset) {
            $doc->encoding = $this->charset;
        }
        
        // CSS verlinken
        if ($this->includeStyle and $this->styleFiles) {
            if (self::$httpConfig['merge-styleFiles']) {
                $this->styleFiles = [
                    $this->getMergedStyleFile($this->styleFiles)
                ];
            } else {
                foreach ($this->styleFiles as $path => &$styleFile) {
                    // $styleFile = '/getStyle.php/' . $path;
                    $styleFile = $this->getMergedStyleFile([
                        $styleFile
                    ]);
                }
                unset($styleFile);
            }
            
            foreach ($this->styleFiles as $styleFile) {
                $doc->insertBefore($doc->createProcessingInstruction('xml-stylesheet', sprintf('type="text/css" href="%s"', $styleFile)), $doc->firstChild);
            }
        }
        
        // Script-Datei erstellen
        if ($this->scriptFiles) {
            if (self::$httpConfig['merge-scriptFiles']) {
                $this->scriptFiles = [
                    $this->getMergedScriptFile($this->scriptFiles)
                ];
            } else {
                foreach ($this->scriptFiles as $path => &$scriptFile) {
                    // $scriptFile = '/getScript.php/' . $path;
                    $scriptFile = $this->getMergedScriptFile([
                        $scriptFile
                    ]);
                }
                unset($scriptFile);
            }
        }
        
        // Sprach-Links erstellen
        $langLinks = [];
        /*
         * foreach ($this->dict->getSupportedLang() as $lang) {
         * $langLinks[$lang] = $this->dict->createLink($this->requestedPage->getAttribute('url'), $lang);
         * }
         * //
         */
        
        // Script-Datei und Sprach-Links Doctype-abhängig verlinken
        $ns = $doc->documentElement ? $doc->documentElement->namespaceURI : null;
        switch ($ns) {
            /*
             * case HTTPDocument::NS_HTML:
             * $this->mime = 'text/html';
             * $this->doctype = $doc->doctype;
             * if (!$this->doctype) {
             * $this->doctype = $doc->implementation->createDocumentType('html');
             * }
             * if ($head = $doc->getElementsByTagNameNS(HTTPDocument::NS_HTML, 'head')->item(0)) {
             * foreach ($langLinks as $lang => $uri) {
             * $node = $doc->createElementNS(HTTPDocument::NS_HTML, 'link');
             * $node->setAttribute('hreflang', $lang);
             * $node->setAttribute('href', $uri);
             * $node->setAttribute('rel', 'alternate');
             * $head->appendChild($node);
             * }
             * } else {
             * $head = $doc->documentElement;
             * }
             * foreach ($this->scriptFiles as $scriptFile) {
             * if ($scriptFile) {
             * $node = $doc->createElementNS(HTTPDocument::NS_HTML, 'script');
             * $node->setAttribute('src', $scriptFile);
             * $node->setAttribute('defer', 'defer');
             * $node->setAttribute('type', 'application/javascript');
             * $head->appendChild($node);
             * }
             * }
             * break;
             * //
             */
            case HTTPDocument::NS_HTML:
                $this->mime = 'application/xhtml+xml';
                $this->doctype = $doc->doctype;
                if (! $this->doctype) {
                    $this->doctype = $doc->implementation->createDocumentType('html');
                }
                if ($head = $doc->getElementsByTagNameNS(HTTPDocument::NS_HTML, 'head')->item(0)) {
                    foreach ($langLinks as $lang => $uri) {
                        $node = $doc->createElementNS(HTTPDocument::NS_HTML, 'link');
                        $node->setAttribute('hreflang', $lang);
                        $node->setAttribute('href', $uri);
                        $node->setAttribute('rel', 'alternate');
                        $head->appendChild($node);
                    }
                } else {
                    $head = $doc->documentElement;
                }
                foreach ($this->scriptFiles as $scriptFile) {
                    if ($scriptFile) {
                        $node = $doc->createElementNS(HTTPDocument::NS_HTML, 'script');
                        $node->setAttribute('src', $scriptFile);
                        $node->setAttribute('defer', 'defer');
                        $node->setAttribute('type', 'application/javascript');
                        $head->appendChild($node);
                    }
                }
                break;
            case HTTPDocument::NS_SVG:
                $this->mime = 'image/svg+xml';
                if ($head = $doc->getElementsByTagNameNS(HTTPDocument::NS_SVG, 'defs')->item(0)) {} else {
                    $head = $doc->documentElement;
                }
                foreach ($this->scriptFiles as $scriptFile) {
                    if ($scriptFile) {
                        $node = $doc->createElementNS(HTTPDocument::NS_SVG, 'script');
                        $node->setAttributeNS(HTTPDocument::NS_XLINK, 'xlink:href', $scriptFile);
                        $head->appendChild($node);
                    }
                }
                // $this->doctype = $doc->implementation->createDocumentType('svg');
                break;
            case HTTPDocument::NS_XSL:
                $this->mime = 'application/xslt+xml';
                break;
            default:
                $this->mime = 'application/xml';
                break;
        }
        /*
         * if ($this->requestedPage->hasAttribute('title')) {
         * $this->fileName = $this->requestedPage->getAttribute('title');
         * } elseif ($this->requestedPage->hasAttribute('name')) {
         * $this->fileName = $this->requestedPage->getAttribute('name');
         * }
         * //
         */
        if ($ext = Resource::getExtension($this->mime)) {
            $this->setFileExt($ext, false);
        }
        $this->setStatus(self::STATUS_OK);
        // $this->addHeader('content-location', $this->requestedPage->getAttribute('uri'));
        // header('last-modified: ' . date(DATE_RFC2822, $this->now)); //todo: cacheControl
        // header('expires: ' . date(DATE_RFC2822, $this->now)); //todo: cacheControl
        
        if ($this->doctype) {
            $doc->insertBefore($this->doctype, $doc->documentElement);
        }
        if (self::$httpConfig['doc-timestamp']) {
            $this->setEtag(self::calcEtag($doc->saveXML()), false);
            $doc->documentElement->insertBefore($doc->createComment(PHP_EOL . sprintf(HTTPDocument::ERR_REQRES, get_execution_time(), memory_get_peak_usage() / 1048576) . PHP_EOL), $doc->documentElement->firstChild);
            $this->setBody(trim($doc->saveXML()));
        } else {
            $this->setBody(trim($doc->saveXML()));
            $this->setEtag(self::calcEtag($this->body), true);
        }
    }

    public function setBody($data)
    {
        $data = (string) $data;
        if (in_array(self::CONTENT_ENCODING_GZIP, $this->contentEncodingList)) {
            $data = gzencode($data, self::$httpConfig['gzip-level'], FORCE_GZIP);
            $this->contentEncoding = self::CONTENT_ENCODING_GZIP;
        } else {
            $this->contentEncoding = self::CONTENT_ENCODING_RAW;
        }
        $this->body = $data;
        $this->bodyLength = strlen($this->body);
        $this->bodyType = self::BODY_STRING;
    }

    public function getBody()
    {
        return $this->body;
    }

    protected function bodyHasChanged()
    {
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

    public function setEtag($etag, $isStrong = true)
    { // https://tools.ietf.org/html/rfc7232#section-2.3
        $etag = sprintf('%s"%s"', $isStrong ? '' : 'W/', $etag);
        $this->addHeader('etag', $etag);
    }

    public function setLastModified($time, $isStrong = true)
    {
        $date = gmdate(self::$httpConfig['date-format'], $time);
        $this->addHeader('last-modified', $date);
    }

    public function setContentEncoding($contentEncoding)
    {
        if ($contentEncodingList = explode(',', $contentEncoding)) {
            foreach ($contentEncodingList as $contentEncoding) {
                $contentEncoding = trim($contentEncoding);
                if (in_array($contentEncoding, $this->supportedContentEncodings)) {
                    $this->contentEncodingList[] = $contentEncoding;
                }
            }
        }
    }

    protected function sendHeaderList()
    {
        //$this->addHeader('connection', 'Keep-Alive');
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
                if ($this->status === self::STATUS_PARTIAL_CONTENT or $this->status === self::STATUS_REQUESTED_RANGE_NOT_SATISFIABLE) {
                    $this->addHeader('content-range', 'bytes %1$.0f-%2$.0f/%3$.0f', [
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
                $cacheDuration = self::$httpConfig['cache-duration'];
                if (strpos($this->mime, 'image/') === 0) {
                    $cacheDuration = TIME_MONTH;
                }
                if (strpos($this->mime, 'application/font') === 0) {
                    $cacheDuration = TIME_YEAR;
                }
                if (strpos($this->mime, 'text/css') === 0) {
                    $cacheDuration = TIME_WEEK;
                }
                if (strpos($this->mime, 'application/javascript') === 0) {
                    $cacheDuration = TIME_WEEK;
                }
                $this->addHeader('cache-control', 'must-revalidate, max-age=%d', [
                    $cacheDuration
                ]);
                break;
            case self::BODY_STREAM:
            case self::BODY_COMMAND:
                $this->addHeader('cache-control', 'private, no-cache');
                break;
        }
        $this->addHeader('vary', implode(', ', $this->supportedNegotiations));
        header(sprintf('%s/%d.%d %d %s', $this->protocolName, $this->protocolMajorVersion, $this->protocolMinorVersion, $this->status, self::$httpStatusCodes[$this->status]), true, $this->status);
        foreach ($this->headerList as $key => $val) {
            header(sprintf('%s:%s', $key, $val));
        }
        flush();
        $this->headerList = [];
    }

    protected function sendBody()
    {
        if ($this->includeBody) {
            set_time_limit(TIME_DAY);
            switch ($this->bodyType) {
                case self::BODY_STRING:
                    if ($this->rangeStart === 0 and $this->rangeEnd === $this->bodyLength) {
                        $this->sendBodyChunk($this->body);
                    } else {
                        $this->sendBodyChunk(substr($this->body, $this->rangeStart, $this->rangeEnd - $this->rangeStart));
                    }
                    break;
                case self::BODY_FILE:
                    $size = $this->bodyLength;
                    $start = $this->rangeStart;
                    $end = $this->rangeEnd;
                    $length = $end - $start;
                    
                    if ($handle = fopen($this->body, 'rb')) {
                        if ($start < PHP_INT_MAX) {
                            fseek($handle, $start, SEEK_SET);
                        } else {
                            fseek($handle, PHP_INT_MAX, SEEK_SET);
                            $start -= PHP_INT_MAX;
                            while ($start > 0) {
                                fread($handle, min(self::$httpConfig['seek-size'], $start));
                                $start -= self::$httpConfig['seek-size'];
                            }
                        }
                        while ($length > 0) {
                            $this->sendBodyChunk(fread($handle, min(self::$httpConfig['file-size'], $length)));
                            $length -= self::$httpConfig['file-size'];
                        }
                        fclose($handle);
                    }
                    break;
                case self::BODY_STREAM:
                    $intervalTime = 0;
                    $timeoutTime = 0;
                    $sleepDuration = $this->body->getSleepDuration();
                    $heartbeatContent = $this->body->getHeartbeatContent();
                    $heartbeatInterval = $this->body->getHeartbeatInterval();
                    $heartbeatTimeout = $this->body->getHeartbeatTimeout();
                    $heartbeatEOL = $this->body->getHeartbeatEOL();
                    $heartbeatSent = false;
                    while (! connection_aborted()) {
                        try {
                            $status = $this->body->getStatus();
                            if ($timeoutTime > $heartbeatTimeout) {
                                throw new UnexpectedValueException('HTTPStream timed out! Aborting stream...');
                            }
                        } catch (Exception $e) {
                            $status = HTTPStream::STATUS_ERROR;
                        }
                        switch ($status) {
                            case HTTPStream::STATUS_CONTENTDONE:
                                $content = $this->body->getContent();
                                if ($content !== '') {
                                    if ($heartbeatSent and $heartbeatEOL !== null) {
                                        $this->sendBodyChunk($heartbeatEOL);
                                        $heartbeatSent = false;
                                    }
                                    $this->sendBodyChunk($content);
                                }
                            // fallthrouuugh /o/
                            case HTTPStream::STATUS_DONE:
                            case HTTPStream::STATUS_ERROR:
                                break 2;
                            case HTTPStream::STATUS_CONTENT:
                                $content = $this->body->getContent();
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
                                usleep($sleepDuration * TIME_USLEEP_FACTOR);
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
                    $this->body->addEventListener('output', function (HTTPEvent $eve) {
                        $this->sendBodyChunk($eve->data);
                    });
                    $this->body->run();
                    break;
            }
            $this->sendBodyEnd();
        }
    }

    protected function sendBodyChunk($chunk)
    {
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

    protected function sendBodyEnd()
    {
        switch ($this->transferEncoding) {
            case self::TRANSFER_ENCODING_RAW:
                break;
            case self::TRANSFER_ENCODING_CHUNKED:
                echo '0' . self::CHUNK_EOL . self::CHUNK_EOL;
                break;
        }
    }
}