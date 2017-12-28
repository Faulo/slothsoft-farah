<?php
/***********************************************************************
 * Slothsoft\Farah\HTTPDocument v1.00 19.10.2012 © Daniel Schulz
 * 
 * 	Changelog:
 *		v1.00 19.10.2012
 *			initial release
 ***********************************************************************/
namespace Slothsoft\Farah;

use Slothsoft\Core\Storage;
use Slothsoft\Core\DOMHelper;
use Slothsoft\PT\Repository;
use DomainException;
use RuntimeException;
use Throwable;
use Exception;
use DOMImplementation;
use DOMDocument;
use DOMElement;
use DOMNode;
use XSLTProcessor;
use Slothsoft\Farah\Tracking\Manager;

declare(ticks = 1000);

class HTTPDocument
{

    const NS_XML = 'http://www.w3.org/XML/1998/namespace';

    const NS_XMLNS = 'http://www.w3.org/2000/xmlns/';

    const NS_XSD = 'http://www.w3.org/2001/XMLSchema';

    const NS_HTML = 'http://www.w3.org/1999/xhtml';

    const NS_XSL = 'http://www.w3.org/1999/XSL/Transform';

    const NS_SVG = 'http://www.w3.org/2000/svg';

    const NS_XLINK = 'http://www.w3.org/1999/xlink';

    const NS_PHP = 'http://php.net/xpath';
    
    const NS_EM = 'http://www.mozilla.org/2004/em-rdf#';

    const DIR_MODULES = 'vendor/slothsoft/';

    const FILE_MODULE = 'module.xml';

    const DIR_DATA = 'data/';

    const DIR_TEMPLATE = 'template/';

    const DIR_STYLESHEET = 'stylesheet/';

    const DIR_SCRIPT = 'script/';

    const DIR_RESSOURCE = 'res/';

    const LOOKUP_PAGE = 'page';

    const LOOKUP_FRAGMENT = 'fragment';

    const LOOKUP_DATA = 'data';

    const LOOKUP_RESOURCE = 'resource';

    const LOOKUP_TEMPLATE = 'template';

    const LOOKUP_CACHE = 'cache';

    const LOOKUP_SCRIPT = 'script';

    const LOOKUP_STYLESHEET = 'style';

    const LOOKUP_DOCUMENT = 'document';

    const TAG_DOMAIN = 'domain';

    const TAG_MODULES = 'modules';

    const TAG_MODULE = 'module';

    const TAG_FRAGMENT = 'fragment';

    const TAG_FRAGMENT_REF = 'call';

    const TAG_RESOURCE = 'resource';

    const TAG_RESOURCE_REF = 'res';

    const TAG_RESOURCEDIR = 'resourceDir';

    const TAG_RESOURCEDIR_REF = 'resDir';

    const TAG_STRUCTURE = 'struc';

    const TAG_STRUCTURE_REF = 'struc';

    const TAG_STYLE_REF = 'style';

    const TAG_SCRIPT_REF = 'script';

    const TAG_DATA = 'data';

    const TAG_DATA_REF = 'data';

    const TAG_TEMPLATE_REF = 'template';

    const TAG_PARAM = 'param';

    const TAG_PAGE = 'page';

    const TAG_TRANSFORMROOT = 'data';

    const MODULE_DEFAULT = 'core';

    const STATUS_CONTINUE = 1;

    const STATUS_RESPONSE_SET = 2;

    const ERR_REQRES = 'Rendering of this page took %d ms and %.2f MB.';

    protected static $lookupURIList = [
        self::LOOKUP_PAGE => '/getPage.php',
        self::LOOKUP_FRAGMENT => '/getFragment.php',
        self::LOOKUP_DATA => '/getData.php',
        self::LOOKUP_RESOURCE => '/getResource.php',
        self::LOOKUP_DOCUMENT => '/getDocument.php',
        self::LOOKUP_TEMPLATE => '/getTemplate.php',
        self::LOOKUP_SCRIPT => '/getScript.php',
        self::LOOKUP_STYLESHEET => '/getStyle.php',
        self::LOOKUP_CACHE => '/getCache.php'
    ];

    private static $singleton;

    /**
     * @return NULL|\Slothsoft\Farah\HTTPDocument
     */
    public static function instance()
    {
        $ret = null;
        if (self::$singleton) {
            $ret = self::$singleton;
        }
        return $ret;
    }

    public static function parseRequest($path, $mode, array $req = null, array $env = null)
    {
        if (! $req) {
            $req = $_REQUEST;
        }
        if (! $env) {
            $env = $_SERVER;
        }
        $request = new HTTPRequest();
        $request->init($env);
        $request->setInput($req);
        $request->setAllHeaders(apache_request_headers());
        $request->setMode($mode);
        $request->setPath($path);
        
        $httpDocument = new HTTPDocument();
        $httpDocument->init(SERVER_ROOT . FILE_SITEMAP);
        
        $response = $httpDocument->lookup($request);
        
		if (constant('CMS_TRACKING_ENABLED')) {
			$track = !$request->hasInputValue('dnt');
			$forceTrack = $request->getInputValue('dnt') === 'false';
			
			foreach (constant('CMS_TRACKING_DNT_URI') as $uri) {
				if (strpos($env['REQUEST_URI'], $uri) === 0) {
					$track = false;
					break;
				}
			}
			
			if ($track or $forceTrack) {
				$dbName = 'cms';
				$tableName = 'access_log';
				try {
					$response->addTrackingInfo($env);
					Manager::track($env);
				} catch(Exception $e) {
				}
			}
		}
		
        $response->send();
    }
	public static function parseRequestTest($path, $mode, array $req, array $env)
    {
        $request = new HTTPRequest();
        $request->init($env);
        $request->setInput($req);
        $request->setAllHeaders([]);
        $request->setMode($mode);
        $request->setPath($path);
        
        $httpDocument = new HTTPDocument();
        $httpDocument->init(SERVER_ROOT . FILE_SITEMAP);
        
        return $httpDocument->lookup($request);
    }

    public static function createRequestURI($path, $mode, array $req = null)
    {
        if (! isset(self::$lookupURIList[$mode])) {
            throw new DomainException('unknown mode: ' . $mode);
        }
        $ret = self::$lookupURIList[$mode];
        $ret .= $path;
        if ($req !== null) {
            $query = http_build_query($req);
            if (strlen($query)) {
                $ret .= '?' . $query;
            }
        }
        return $ret;
    }

    public static function modeByURI($uri)
    {
        $ret = self::LOOKUP_PAGE;
        foreach (self::$lookupURIList as $mode => $test) {
            if (strpos($uri, $test) === 0) {
                $ret = $mode;
                break;
            }
        }
        return $ret;
    }

    public static function loadDocument($filePath)
    {
        return DOMHelper::loadDocument($filePath);
    }

    public static function loadXPath(DOMDocument $document)
    {
        return DOMHelper::loadXPath($document, DOMHelper::XPATH_HTML | DOMHelper::XPATH_PHP);
    }

    public static function loadExternalDocument($uri, $type = 'xml', $cacheTime = TIME_DAY, $data = null, $method = 'GET')
    {
        return Storage::loadExternalDocument($uri, $cacheTime, $data, $method);
    }

    public static function clearExternalDocument($uri, $type = 'xml', $cacheTime = TIME_DAY, $data = null, $method = 'GET')
    {
        return Storage::clearExternalDocument($uri, $cacheTime, $data, $method);
    }

    public static function loadExternalXPath($uri, $cacheTime = TIME_DAY, $data = null, $method = 'GET')
    {
        return Storage::loadExternalXPath($uri, $cacheTime, $data, $method);
    }

    public static function loadExternalJSON($uri, $cacheTime = TIME_DAY, $data = null, $method = 'GET')
    {
        return Storage::loadExternalJSON($uri, $cacheTime, $data, $method);
    }

    public static function loadExternalFile($uri, $cacheTime = TIME_DAY, $data = null, $method = 'GET')
    {
        return Storage::loadExternalFile($uri, $cacheTime, $data, $method);
    }

    public static function loadExternalHeader($uri, $cacheTime = TIME_DAY, $data = null)
    {
        return Storage::loadExternalHeader($uri, $cacheTime, $data);
    }

    public static function normalizeDocument(DOMDocument $dataDoc)
    {
        // $dataDoc->loadXML($dataDoc->saveXML(), LIBXML_NSCLEAN);
        return $dataDoc;
        
        try {
            $retDoc = new DOMDocument();
            
            $nsList = [];
            $nsList[self::NS_XML] = 'xml';
            $nsList[self::NS_HTML] = 'html';
            $nsList[self::NS_SVG] = 'svg';
            $nsList[self::NS_XLINK] = 'xlink';
            $nsList[self::NS_XSL] = 'xsl';
            $nsList[self::NS_EM] = 'em';
            if (isset($nsList[$dataDoc->documentElement->namespaceURI])) {
                unset($nsList[$dataDoc->documentElement->namespaceURI]);
            }
            
            self::normalizeNode($dataDoc, $retDoc, $nsList);
            $retDoc->loadXML($retDoc->saveXML(), LIBXML_NSCLEAN);
        } catch (Exception $e) {
            $retDoc = $dataDoc;
        }
        return $retDoc;
    }

    protected static function normalizeNode(DOMNode $sourceNode, DOMDocument $retDoc, array $nsList)
    {
        $retNode = null;
        switch ($sourceNode->nodeType) {
            case XML_DOCUMENT_NODE:
                $retNode = $retDoc;
                break;
            case XML_ELEMENT_NODE:
                $tagName = isset($nsList[$sourceNode->namespaceURI]) ? $nsList[$sourceNode->namespaceURI] . ':' . $sourceNode->localName : $sourceNode->localName;
                $retNode = $retDoc->createElementNS($sourceNode->namespaceURI, $tagName);
                foreach ($sourceNode->attributes as $childNode) {
                    $tagName = strlen($childNode->prefix) ? $childNode->prefix . ':' . $childNode->localName : $childNode->localName;
                    $retNode->setAttributeNS($childNode->namespaceURI, $tagName, $childNode->value);
                }
                break;
            default:
                $retNode = $retDoc->importNode($sourceNode, false);
                break;
        }
        if ($retNode and $sourceNode->hasChildNodes()) {
            foreach ($sourceNode->childNodes as $childNode) {
                if ($node = self::normalizeNode($childNode, $retDoc, $nsList)) {
                    $retNode->appendChild($node);
                }
            }
        }
        return $retNode;
    }

    private $httpRequest;

    private $httpResponse;

    private $session;

    private $dataDocs = [];
 // DOMDocument[]
    private $templateDocs = [];
 // DOMDocument[]
    private $fragmentDocs = [];
 // DOMDocument[]
    private $resourceDocs = [];
 // DOMDocument[]
    private $resourceDirs = [];
 // DOMDocument[][]
    private $sitesDoc;
 // DOMDocument
    private $sitesPath;
 // DOMXPath
    private $moduleRoot;
 // DOMElement <modules>
    private $requestElement;
 // DOMElement <request>
    private $requestedDomain;
 // DOMElement
    private $requestedPage;
 // DOMElement
    private $requestedNS;
 // string
    private $currentElement;
 // DOMElement
    private $impl;
 // DOMImplementation
    private $xslt;
 // XSLTProcessor
    private $dict;

    public $includeDir;

    public $uriDir;

    private $indent = 'no';

    private $method;

    private $version;

    private $now;

    private $progressStatus = self::STATUS_CONTINUE;

    private $pageTitles = [];

    private $loadedModules = [];

    private $redirectUp = false;

    private $redirectUpAgents = [
        'Googlebot',
        'bingbot'
    ];

    public function __construct()
    {
        self::$singleton = $this;
    }

    public function init($siteMapPath)
    {
        $this->includeDir = SERVER_ROOT . self::DIR_MODULES;
        $this->uriDir = '/' . self::DIR_MODULES;
        $this->method = 'xml';
        $this->version = '1.0';
        
        $this->session = new Session();
        
        $this->impl = new DOMImplementation();
        $this->xslt = new XSLTProcessor();
        $this->xslt->registerPHPFunctions();
        
        $this->sitesDoc = DOMHelper::loadDocument($siteMapPath);
        $this->sitesPath = DOMHelper::loadXPath($this->sitesDoc, DOMHelper::XPATH_PHP | DOMHelper::XPATH_SLOTHSOFT);
        // $this->sitesInitialized = false;
        // $this->resourceDocs['/core/sites'] = $this->sitesDoc;
        $this->moduleRoot = $this->sitesDoc->createElementNS(DOMHelper::NS_CMS_MODULE, self::TAG_MODULES);
    }

    public function getExternalDocument($uri, $type = 'xml', $cacheTime = TIME_DAY)
    {
        return self::loadExternalDocument($uri, $type, $cacheTime);
    }

    public function name2tag($name)
    {
        return str_replace('.', '-', $name);
    }

    public function path2expr($path, $elementName = '*')
    {
        $path = array_filter(explode('/', $path), 'strlen');
        $qry = [
            '.'
        ];
        foreach ($path as $folder) {
            $qry[] = $elementName . '[php:functionString("strtolower", @name) = "' . strtolower($folder) . '" or ancestor-or-self::*/@name = "*"]';
        }
        return implode('/', $qry);
    }

    public function canonicalizePath(&$file, &$folder)
    {
        if (strlen($file) and $file[0] === '/') {
            $folder = '';
        }
        $ret = array_filter(explode('/', $folder . '/' . $file), 'strlen');
        $arr = $ret;
        $folder = array_shift($arr);
        $file = implode('/', $arr);
        return $ret;
    }

    public function getBannedList()
    {
        $logFile = SERVER_ROOT . DIR_LOG . 'banned-ips.txt';
        return file_exists($logFile) ? file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) : [];
    }

    public function setBannedList(array $list)
    {
        $logFile = SERVER_ROOT . DIR_LOG . 'banned-ips.txt';
        return file_put_contents($logFile, implode(PHP_EOL, $list));
    }

    public function isBanworthy($message)
    {
        // TODO: proper hate speech check
        return (preg_match('/nigg[ae]/u', $message) or preg_match('/fags/u', $message) or preg_match('/卐/u', $message));
    }

    public function isBanned($ip = null)
    {
        if (! $ip) {
            $ip = $this->httpRequest->clientIp;
        }
        return in_array($ip, $this->getBannedList(), true);
    }

    public function addBanned($ip = null)
    {
        if (! $ip) {
            $ip = $this->httpRequest->clientIp;
        }
        $this->setBannedList(array_merge($this->getBannedList(), [
            $ip
        ]));
    }

    public function removeBanned($ip = null)
    {
        if (! $ip) {
            $ip = $this->httpRequest->clientIp;
        }
        $this->setBannedList(array_diff($this->getBannedList(), [
            $ip
        ]));
    }

    public function lookup(HTTPRequest $request)
    {
        $this->httpRequest = $request;
        $this->httpResponse = new HTTPResponse();
        $this->httpResponse->setRequest($this->httpRequest);
        
        $this->now = $this->httpRequest->time;
        $this->dict = $this->httpRequest->dict;
        
        $this->lookupDomain($this->httpRequest->clientHost);
        
        if ($this->isBanned($this->httpRequest->clientIp)) {
            // BANHAMMER
            $this->httpResponse->setStatus(HTTPResponse::STATUS_PRECONDITION_FAILED, 'You have been found wanting.');
            return $this->httpResponse;
        }
        
        if ($this->requestedDomain) {
            if ($this->requestedDomain->hasAttribute('dict-languages')) {
                $this->dict->setSupportedLanguages($this->requestedDomain->getAttribute('dict-languages'));
            }
            $this->requestedDomain->setAttribute('active', '');
            if ($this->requestedDomain->getAttribute('name') === $this->httpRequest->clientHost or strtolower($this->httpRequest->clientHost) === strtolower(SERVER_NAME)) {
                /*
                 * $nodeList = $this->sitesPath->evaluate(sprintf('. | .//%s', self::TAG_PAGE), $this->requestedDomain);
                 * foreach ($nodeList as $node) {
                 * $node->setAttribute('uri', $this->findUri($node));
                 * $node->setAttribute('url', $this->findUri($node, true));
                 * }
                 * //
                 */
                $this->requestElement = $this->httpRequest->asNode($this->sitesDoc);
                $this->sitesDoc->documentElement->appendChild($this->requestElement);
                
                if ($this->httpRequest->protocolRecognised) {
                    switch ($this->httpRequest->protocolName) {
                        case HTTPRequest::PROTOCOL_HTTP:
                            if ($this->httpRequest->protocolMajorVersion >= 1 and $this->httpRequest->protocolMinorVersion >= 0) {
                                switch ($this->httpRequest->method) {
                                    case HTTPRequest::METHOD_HEAD:
                                    case HTTPRequest::METHOD_GET:
                                    case HTTPRequest::METHOD_POST:
                                        $ret = null;
                                        $this->httpResponse->setStatus(HTTPResponse::STATUS_GONE);
                                        $this->httpResponse->setDownload(isset($this->httpRequest->input['download']));
                                        foreach ($this->redirectUpAgents as $agent) {
                                            if (strpos($this->httpRequest->clientAgent, $agent) !== false) {
                                                $this->redirectUp = true;
                                                break;
                                            }
                                        }
                                        
                                        switch ($this->httpRequest->mode) {
                                            case self::LOOKUP_PAGE:
                                                $ret = $this->loadPage();
                                                break;
                                            case self::LOOKUP_FRAGMENT:
                                                $ret = $this->loadFragment();
                                                break;
                                            case self::LOOKUP_DATA:
                                                $ret = $this->loadData();
                                                break;
                                            case self::LOOKUP_RESOURCE:
                                                $ret = $this->loadResource();
                                                break;
                                            case self::LOOKUP_DOCUMENT:
                                                $ret = $this->loadPTDocument();
                                                break;
                                            case self::LOOKUP_TEMPLATE:
                                                $ret = $this->loadTemplate();
                                                break;
                                            case self::LOOKUP_SCRIPT:
                                                $ret = $this->loadScript();
                                                break;
                                            case self::LOOKUP_STYLESHEET:
                                                $ret = $this->loadStyle();
                                                break;
                                            case self::LOOKUP_CACHE:
                                                $ret = $this->loadCache();
                                                break;
                                        }
                                        if (! ($this->progressStatus & self::STATUS_RESPONSE_SET)) {
                                            switch (true) {
                                                case $ret instanceof DOMDocument:
                                                    $this->httpResponse->setDocument($ret);
                                                    $this->progressStatus |= self::STATUS_RESPONSE_SET;
                                                    break;
                                                case $ret instanceof HTTPFile:
                                                    $this->httpResponse->setFile($ret->getPath(), $ret->getName());
                                                    $this->progressStatus |= self::STATUS_RESPONSE_SET;
                                                    break;
                                                case $ret instanceof HTTPStream:
                                                    $this->httpResponse->setStream($ret);
                                                    $this->progressStatus |= self::STATUS_RESPONSE_SET;
                                                    break;
                                                case is_file($ret):
                                                    $this->httpResponse->setFile($ret);
                                                    $this->progressStatus |= self::STATUS_RESPONSE_SET;
                                                    break;
                                            }
                                        }
                                        break;
                                    default:
                                        $this->httpResponse->setStatus(HTTPResponse::STATUS_NOT_IMPLEMENTED);
                                        break;
                                }
                            } else {
                                $this->httpResponse->setStatus(HTTPResponse::STATUS_HTTP_VERSION_NOT_SUPPORTED);
                            }
                            break;
                        default:
                            $this->httpResponse->setStatus(HTTPResponse::STATUS_METHOD_NOT_ALLOWED);
                            break;
                    }
                } else {
                    $this->httpResponse->setStatus(HTTPResponse::STATUS_BAD_REQUEST);
                }
            } else {
                $uri = 'http://' . $this->requestedDomain->getAttribute('name') . $_SERVER['REQUEST_URI'];
                $this->httpResponse->setMoved($uri, true);
            }
        } else {
            $this->httpResponse->setStatus(HTTPResponse::STATUS_NOT_FOUND);
        }
        
        return $this->httpResponse;
    }
	public function lookupTest(HTTPRequest $request)
    {
        $this->httpRequest = $request;
        $this->httpResponse = new HTTPResponse();
        $this->httpResponse->setRequest($this->httpRequest);
        
        $this->now = $this->httpRequest->time;
        $this->dict = $this->httpRequest->dict;
        
        $this->lookupDomain($this->httpRequest->clientHost);
        
        if ($this->isBanned($this->httpRequest->clientIp)) {
            // BANHAMMER
            $this->httpResponse->setStatus(HTTPResponse::STATUS_PRECONDITION_FAILED, 'You have been found wanting.');
            return $this->httpResponse;
        }
        
        if ($this->requestedDomain) {
            if ($this->requestedDomain->hasAttribute('dict-languages')) {
                $this->dict->setSupportedLanguages($this->requestedDomain->getAttribute('dict-languages'));
            }
            $this->requestedDomain->setAttribute('active', '');
            if ($this->requestedDomain->getAttribute('name') === $this->httpRequest->clientHost or strtolower($this->httpRequest->clientHost) === strtolower(SERVER_NAME)) {
                /*
                 * $nodeList = $this->sitesPath->evaluate(sprintf('. | .//%s', self::TAG_PAGE), $this->requestedDomain);
                 * foreach ($nodeList as $node) {
                 * $node->setAttribute('uri', $this->findUri($node));
                 * $node->setAttribute('url', $this->findUri($node, true));
                 * }
                 * //
                 */
                $this->requestElement = $this->httpRequest->asNode($this->sitesDoc);
                $this->sitesDoc->documentElement->appendChild($this->requestElement);
                
                if ($this->httpRequest->protocolRecognised) {
                    switch ($this->httpRequest->protocolName) {
                        case HTTPRequest::PROTOCOL_HTTP:
                            if ($this->httpRequest->protocolMajorVersion >= 1 and $this->httpRequest->protocolMinorVersion >= 0) {
                                switch ($this->httpRequest->method) {
                                    case HTTPRequest::METHOD_HEAD:
                                    case HTTPRequest::METHOD_GET:
                                    case HTTPRequest::METHOD_POST:
                                        $ret = null;
                                        $this->httpResponse->setStatus(HTTPResponse::STATUS_GONE);
                                        $this->httpResponse->setDownload(isset($this->httpRequest->input['download']));
                                        foreach ($this->redirectUpAgents as $agent) {
                                            if (strpos($this->httpRequest->clientAgent, $agent) !== false) {
                                                $this->redirectUp = true;
                                                break;
                                            }
                                        }
                                        
                                        switch ($this->httpRequest->mode) {
                                            case self::LOOKUP_PAGE:
                                                $ret = $this->loadPage();
                                                break;
                                            case self::LOOKUP_FRAGMENT:
                                                $ret = $this->loadFragment();
                                                break;
                                            case self::LOOKUP_DATA:
                                                $ret = $this->loadData();
                                                break;
                                            case self::LOOKUP_RESOURCE:
                                                $ret = $this->loadResource();
                                                break;
                                            case self::LOOKUP_DOCUMENT:
                                                $ret = $this->loadPTDocument();
                                                break;
                                            case self::LOOKUP_TEMPLATE:
                                                $ret = $this->loadTemplate();
                                                break;
                                            case self::LOOKUP_SCRIPT:
                                                $ret = $this->loadScript();
                                                break;
                                            case self::LOOKUP_STYLESHEET:
                                                $ret = $this->loadStyle();
                                                break;
                                            case self::LOOKUP_CACHE:
                                                $ret = $this->loadCache();
                                                break;
                                        }
                                        if (! ($this->progressStatus & self::STATUS_RESPONSE_SET)) {
                                            switch (true) {
                                                case $ret instanceof DOMDocument:
                                                    $this->httpResponse->setDocument($ret);
                                                    $this->progressStatus |= self::STATUS_RESPONSE_SET;
                                                    break;
                                                case $ret instanceof HTTPFile:
                                                    $this->httpResponse->setFile($ret->getPath(), $ret->getName());
                                                    $this->progressStatus |= self::STATUS_RESPONSE_SET;
                                                    break;
                                                case $ret instanceof HTTPStream:
                                                    $this->httpResponse->setStream($ret);
                                                    $this->progressStatus |= self::STATUS_RESPONSE_SET;
                                                    break;
                                                case is_file($ret):
                                                    $this->httpResponse->setFile($ret);
                                                    $this->progressStatus |= self::STATUS_RESPONSE_SET;
                                                    break;
                                            }
                                        }
                                        break;
                                    default:
                                        $this->httpResponse->setStatus(HTTPResponse::STATUS_NOT_IMPLEMENTED);
                                        break;
                                }
                            } else {
                                $this->httpResponse->setStatus(HTTPResponse::STATUS_HTTP_VERSION_NOT_SUPPORTED);
                            }
                            break;
                        default:
                            $this->httpResponse->setStatus(HTTPResponse::STATUS_METHOD_NOT_ALLOWED);
                            break;
                    }
                } else {
                    $this->httpResponse->setStatus(HTTPResponse::STATUS_BAD_REQUEST);
                }
            } else {
                $uri = 'http://' . $this->requestedDomain->getAttribute('name') . $_SERVER['REQUEST_URI'];
                $this->httpResponse->setMoved($uri, true);
            }
        } else {
            $this->httpResponse->setStatus(HTTPResponse::STATUS_NOT_FOUND);
        }
        
        return $this->httpResponse;
    }

    private function lookupDomain($domain)
    {
        $nodeList = $this->sitesPath->evaluate(sprintf('%s[@default][1]', self::TAG_DOMAIN), $this->sitesDoc->documentElement);
        foreach ($nodeList as $node) {
            $this->requestedDomain = $node;
        }
        $nodeList = $this->sitesPath->evaluate(sprintf('%s[@name = "%s"][1]', self::TAG_DOMAIN, $domain), $this->sitesDoc->documentElement);
        foreach ($nodeList as $node) {
            $this->requestedDomain = $node;
        }
        $refQuery = sprintf('.//%s | .//%s | .//%s', self::TAG_DATA_REF, self::TAG_RESOURCE_REF, self::TAG_FRAGMENT_REF);
        while ($nodeList = $this->sitesPath->evaluate($refQuery, $this->requestedDomain) and $nodeList->length) {
            $dataList = [];
            foreach ($nodeList as $node) {
                $dataList[] = $node;
            }
            foreach ($dataList as $dataNode) {
                if ($path = $dataNode->getAttribute('ref')) {
                    $dataDoc = null;
                    switch ($dataNode->tagName) {
                        case self::TAG_DATA_REF:
                            $dataDoc = $this->getDataDoc($path);
                            break;
                        case self::TAG_RESOURCE_REF:
                            $dataDoc = $this->getResourceDoc($path, 'document');
                            break;
                        case self::TAG_FRAGMENT_REF:
                            $dataDoc = $this->getFragmentDoc($path);
                            break;
                    }
                    if ($dataDoc) {
                        $childList = [];
                        foreach ($dataDoc->documentElement->childNodes as $childNode) {
                            $childList[] = $childNode;
                        }
                        $this->appendNodes($dataNode->parentNode, $childList, $dataNode);
                    }
                }
                $dataNode->parentNode->removeChild($dataNode);
            }
        }
        $nodeList = $this->sitesPath->evaluate(sprintf('. | .//%s', self::TAG_PAGE), $this->requestedDomain);
        foreach ($nodeList as $node) {
            $node->setAttribute('uri', $this->findUri($node));
            $node->setAttribute('url', $this->findUri($node, true));
        }
        /*
         * while ($nodeList = $this->sitesPath->evaluate(sprintf('.//%s', self::TAG_DATA_REF), $this->requestedDomain) and $nodeList->length) {
         * $dataList = [];
         * foreach ($nodeList as $node) {
         * $dataList[] = $node;
         * }
         * foreach ($dataList as $dataNode) {
         * if ($path = $dataNode->getAttribute('ref') and $dataDoc = $this->getDataDoc($path)) {
         * $childList = [];
         * foreach ($dataDoc->documentElement->childNodes as $childNode) {
         * $childList[] = $childNode;
         * }
         * $this->appendNodes($dataNode->parentNode, $childList, $dataNode);
         * }
         * $dataNode->parentNode->removeChild($dataNode);
         * }
         * }
         * while ($nodeList = $this->sitesPath->evaluate(sprintf('.//%s', self::TAG_RESOURCE_REF), $this->requestedDomain) and $nodeList->length) {
         * $dataList = [];
         * foreach ($nodeList as $node) {
         * $dataList[] = $node;
         * }
         * foreach ($dataList as $dataNode) {
         * if ($path = $dataNode->getAttribute('ref') and $dataDoc = $this->getResourceDoc($path, 'document')) {
         * $childList = [];
         * foreach ($dataDoc->documentElement->childNodes as $childNode) {
         * $childList[] = $childNode;
         * }
         * $this->appendNodes($dataNode->parentNode, $childList, $dataNode);
         * }
         * $dataNode->parentNode->removeChild($dataNode);
         * }
         * }
         * //
         */
    }

    private function loadPage()
    {
        $this->requestedPage = $this->findPage($this->httpRequest->path, $this->requestedDomain);
        if ($this->requestedDomain and $this->requestedPage) {
            $this->requestedNS = $this->getOwnerModule($this->requestedPage);
            for ($node = $this->requestedPage; $node !== $node->ownerDocument->documentElement; $node = $node->parentNode) {
                if ($node->hasAttribute('title')) {
                    // $this->pageTitles[] = $this->dict->lookupText($node->getAttribute('title'));
                    $this->pageTitles[] = $node;
                }
                $this->parseParam($node);
            }
            $this->requestedPage->setAttribute('current', '1');
            foreach ($this->pageTitles as $title) {
                $this->requestElement->appendChild($title->cloneNode(false));
            }
            if ($this->requestedPage->hasAttribute('ref')) {
                $ref = $this->requestedPage->getAttribute('ref');
                $ns = $this->requestedNS;
                $path = $this->canonicalizePath($ref, $ns);
                $this->httpRequest->path = implode('/', $path);
                $this->httpResponse->addHeader('content-location', $this->findUri($this->requestedPage)); // $this->requestedPage->getAttribute('uri'));
                if ($dataDoc = $this->loadFragment()) {
                    if (isset($this->httpRequest->input['appcache'])) {
                        $this->httpResponse->setStatus(200);
                        $this->httpResponse->setBody($this->httpResponse->getCacheManifestContent());
                        $this->httpResponse->setFileName('manifest.appcache');
                        $this->progressStatus |= self::STATUS_RESPONSE_SET;
                        return null;
                    }
                    $nodeList = $dataDoc->documentElement->getElementsByTagName('*');
                    foreach ($nodeList as $node) {
                        $dataDoc->replaceChild($node, $dataDoc->documentElement);
                        $dataDoc = self::normalizeDocument($dataDoc);
                        return $dataDoc;
                    }
                }
            }
        }
        return null;
    }

    private function loadFragment()
    {
        $tmpDoc = $this->getFragmentDoc($this->httpRequest->path);
        if (isset($this->httpRequest->input['standalone']) and $tmpDoc->documentElement->firstChild) {
            $tmpDoc->replaceChild($tmpDoc->documentElement->firstChild, $tmpDoc->documentElement);
        }
        return $tmpDoc;
    }

    private function loadData()
    {
        return $this->getDataDoc($this->httpRequest->path);
    }

    private function loadResource()
    {
        $ret = null;
        if (isset($this->httpRequest->input['load'])) {
            $load = $this->httpRequest->input['load'];
            $name = $this->httpRequest->path;
            $module = '';
            $path = $this->canonicalizePath($name, $module);
            $path = explode('/', $this->httpRequest->path);
            if ($path[0] === '') {
                array_shift($path);
            }
            if (count($path) > 2) {
                $resName = array_pop($path);
                $path = implode('/', $path);
                if ($resNode = $this->getModuleNode($path, self::TAG_RESOURCEDIR)) {
                    $resList = $this->getResourceDir($path, $load);
                    if (strlen($resName)) {
                        /*
                         * $ext = $resNode->hasAttribute('ext')
                         * ? $resNode->getAttribute('ext')
                         * : (string) Resource::getExtension($resNode->getAttribute('type'));
                         * if (strlen($ext)) {
                         * $resName .= '.' . $ext;
                         * }
                         * //
                         */
                        if (isset($resList[$resName])) {
                            $ret = $resList[$resName];
                        }
                    } else {
                        $ret = new DOMDocument();
                        $ret->appendChild($ret->importNode($resNode, false));
                        foreach ($resList as $doc) {
                            $ret->documentElement->appendChild($ret->importNode($doc->documentElement, true));
                        }
                    }
                }
            } else {
                $path = implode('/', $path);
                $ret = $this->getResourceDoc($path, $load);
            }
        } else {
            $name = $this->httpRequest->path;
            $module = '';
            $path = $this->canonicalizePath($name, $module);
            if (count($path) > 2) {
                $resName = array_pop($path);
                $path = implode('/', $path);
                if ($resNode = $this->getModuleNode($path, self::TAG_RESOURCEDIR)) {
                    $ret = $this->includeDir . $module . '/' . self::DIR_RESSOURCE . $resNode->getAttribute('path') . '/' . $resName;
                    $ext = $resNode->hasAttribute('ext') ? $resNode->getAttribute('ext') : (string) Resource::getExtension($resNode->getAttribute('type'));
                    if (strlen($ext)) {
                        $ret .= '.' . $ext;
                    }
                    $ret = utf8_decode($ret);
                }
            } else {
                $path = implode('/', $path);
                if ($resNode = $this->getModuleNode($path, self::TAG_RESOURCE)) {
                    $ret = $this->includeDir . $module . '/' . self::DIR_RESSOURCE . $resNode->getAttribute('path');
                    $ret = utf8_decode($ret);
                }
            }
        }
        return $ret;
    }

    private function loadPTDocument()
    {
        $ret = null;
        $name = $this->httpRequest->path;
        $module = '';
        $this->canonicalizePath($name, $module);
        $repo = Repository::getInstance($module);
        if ($repo->hasDocument($name)) {
            $doc = $repo->getDocument($name);
            $ret = $doc->saveDOMDocument();
        }
        return $ret;
    }

    private function loadTemplate()
    {
        return $this->getTemplateDoc($this->httpRequest->path);
    }

    private function loadScript()
    {
        $name = $this->httpRequest->path;
        $module = '';
        $this->canonicalizePath($name, $module);
        return $this->includeDir . $module . '/' . self::DIR_SCRIPT . $name . '.js';
    }

    private function loadStyle()
    {
        $name = $this->httpRequest->path;
        $module = '';
        $this->canonicalizePath($name, $module);
        return $this->includeDir . $module . '/' . self::DIR_STYLESHEET . $name . '.css';
    }

    private function loadCache()
    {
        $cache = new Cache();
        $file = $cache->getFile($this->httpRequest->path);
        return $file;
    }

    private function loadModule($module)
    {
        $file = SERVER_ROOT . sprintf('%s%s/%s', self::DIR_MODULES, $module, self::FILE_MODULE);
        if (isset($this->loadedModules[$file])) {
            return true;
        }
        if (! is_file($file)) {
            return false;
        }
        $doc = DOMHelper::loadDocument($file);
        /*
         * $xpath = self::loadXPath($doc);
         * $res = $xpath->evaluate('//*[@struc]');
         * if ($res->length) {
         * $nodeList = [];
         * foreach ($res as $parentNode) {
         * $nodeList[] = $parentNode;
         * }
         * foreach ($nodeList as $parentNode) {
         * $node = $doc->createElement(self::TAG_STRUCTURE_REF);
         * $node->setAttribute('name', $parentNode->getAttribute('struc'));
         * $parentNode->insertBefore($node, $parentNode->firstChild);
         * $parentNode->removeAttribute('struc');
         * }
         * $doc->save($file);
         * die($file);
         * }
         * //
         */
        $doc->documentElement->setAttribute('module', $module);
        $this->moduleRoot->appendChild($this->sitesDoc->importNode($doc->documentElement, true));
        $this->loadedModules[$file] = true;
        return true;
    }

    private function getOwnerModule(DOMElement $node)
    {
        $ret = $this->sitesPath->evaluate('string(ancestor-or-self::*[@module][1]/@module)', $node);
        return strlen($ret) ? $ret : self::MODULE_DEFAULT;
    }

    private function setOwnerModule(DOMElement $targetNode, DOMElement $sourceNode)
    {
        $targetNode->setAttribute('module', $this->getOwnerModule($sourceNode));
    }

    private function getModuleNode($path, $elementTag = '*')
    {
        $retNode = null;
        if (strlen($path) and $path[0] === '/') {
            $path = substr($path, 1);
        }
        if ($path = explode('/', $path)) {
            $moduleName = array_shift($path);
            $this->loadModule($moduleName);
            $query = [];
            $query[] = sprintf('%s[@module = %s]', self::TAG_MODULE, $this->xpathQuote($moduleName));
            foreach ($path as $elementName) {
                $query[] = sprintf('%s[@name = %s]', $elementTag, $this->xpathQuote($elementName));
            }
            $query = implode('/', $query);
            $res = $this->sitesPath->evaluate($query, $this->moduleRoot);
            if ($res->length) {
                $retNode = $res->item(0);
            }
        }
        return $retNode;
    }

    private function getModulePath(DOMElement $targetNode)
    {
        $ret = [];
        for (true; $targetNode->tagName !== self::TAG_MODULE; $targetNode = $targetNode->parentNode) {
            $ret[] = $targetNode->getAttribute('name');
        }
        $ret[] = $targetNode->getAttribute('module');
        return implode('/', array_reverse($ret));
    }

    private function splitModulePath(&$path)
    {
        $ret = [
            '',
            ''
        ];
        $arr = array_filter(explode('/', $path . '/'), 'strlen');
        $ret[0] = array_shift($arr);
        $ret[1] = implode('/', $arr);
        $path = sprintf('/%s/%s', $ret[0], $ret[1]);
        return $ret;
    }

    private function findPage($path, DOMElement $contextNode)
    {
        $ret = null;
        $redirected = false;
        $path = str_pad($path, 1, '/');
        if ($qry = $this->path2expr($path, self::TAG_PAGE)) {
            if ($path[0] === '/') {
                $contextNode = $this->requestedDomain;
            }
            $res = $this->sitesPath->evaluate($qry, $contextNode);
            // my_dump($qry);my_dump($res->length);
            if ($res->length) {
                $ret = $res->item(0);
            }
        }
        if ($ret and $ret->hasAttribute('redirect')) {
            $redirect = $ret->getAttribute('redirect');
            switch ($redirect) {
                case '..':
                    $ret = $ret->parentNode;
                    break;
                default:
                    $ret = $this->findPage($redirect, $ret);
                    break;
            }
            $redirected = true;
        }
        if ($ret and $ret->hasAttribute('ext')) {
            $this->httpResponse->setRedirect($ret->getAttribute('ext'), true);
            $ret = null;
        } else {
            if (! $ret) {
                if ($this->redirectUp and $path !== '/') {
                    $path = substr($path, 0, strrpos($path, '/'));
                    $ret = $this->findPage($path, $contextNode);
                    $redirected = true;
                } else {
                    $this->httpResponse->setStatus(HTTPResponse::STATUS_GONE);
                }
            }
            if ($ret and $redirected) {
                $this->httpResponse->setRedirect($this->findUri($ret, true), true);
                $ret = null;
            }
        }
        return $ret;
    }

    private function findUri(DOMElement $pageNode, $includeDomain = false)
    {
        if ($pageNode->hasAttribute('ext')) {
            return $pageNode->getAttribute('ext');
        }
        $ret = '/';
        $tmpNode = $pageNode;
        while ($tmpNode and $tmpNode->tagName !== self::TAG_DOMAIN) {
            if ($tmpNode->hasAttribute('uri') and ! $tmpNode->hasAttribute('redirect')) {
                $ret = $tmpNode->getAttribute('uri') . substr($ret, 1);
                break;
            }
            $ret = '/' . $tmpNode->getAttribute('name') . $ret;
            $tmpNode = $tmpNode->parentNode;
        }
        if ($pageNode->hasAttribute('redirect')) {
            $redirect = $pageNode->getAttribute('redirect');
            if ($redirect[0] === '/') {
                $ret = $redirect;
            } else {
                $ret = explode('/', $ret);
                $redirect = explode('/', $redirect);
                array_pop($ret);
                while (reset($redirect) === '..') {
                    array_pop($ret);
                    array_shift($redirect);
                }
                $ret[] = '';
                $redirect[] = '';
                $ret = implode('/', $ret) . implode('/', $redirect);
            }
        }
        // $ret = '/' . $ret;
        return $includeDomain ? $this->httpRequest->schema . '://' . $this->requestedDomain->getAttribute('name') . $ret : $ret;
    }

    private function transform(DOMDocument $dataDoc, DOMDocument $templateDoc)
    {
		/* //lets be real... nobody cares
        $outputElements = $templateDoc->getElementsByTagNameNS(self::NS_XSL, 'output');
        foreach ($outputElements as $outputElement) {
            // $outputElement->setAttribute('media-type', $this->mime);
            $outputElement->setAttribute('method', $this->method);
            // $outputElement->setAttribute('encoding', $this->charset);
            $outputElement->setAttribute('version', $this->version);
            $outputElement->setAttribute('indent', $this->indent);
        }
		//*/
        $this->xslt->importStylesheet($templateDoc);
        return $this->xslt->transformToDoc($dataDoc);
    }

    private function appendNodes(DOMElement $dataRoot, $object, DOMNode $refNode = null)
    {
        if (is_array($object)) {
            foreach ($object as $tmp) {
                $this->appendNodes($dataRoot, $tmp, $refNode);
            }
        } else {
            $dataDoc = $dataRoot->ownerDocument;
            if ($object instanceof DOMNode) {
                switch ($object->nodeType) {
                    case XML_PI_NODE:
                        if ($object->ownerDocument !== $dataDoc) {
                            $object = $dataDoc->importNode($object, true);
                        }
                        $dataDoc->insertBefore($object, $dataDoc->firstChild);
                        break;
                    case XML_DOCUMENT_NODE:
                    case XML_DOCUMENT_FRAG_NODE:
                    case XML_HTML_DOCUMENT_NODE:
                        $tmpList = [];
                        foreach ($object->childNodes as $tmp) {
                            $tmpList[] = $tmp;
                        }
                        $this->appendNodes($dataRoot, $tmpList);
                        break;
                    case XML_ELEMENT_NODE:
                        if ($object->ownerDocument !== $dataDoc) {
                            $object = $dataDoc->importNode($object, true);
                        }
                        $dataRoot->insertBefore($object, $refNode);
                        break;
                }
            }
        }
    }

    private function xpathQuote($str)
    {
        return sprintf('"%s"', str_replace('"', '', $str));
    }

    private function includeFile($fileName, DOMDocument $dataDoc)
    {
        return include ($fileName);
    }

    public function getResourcePath($path)
    {
        $ret = null;
        list ($module, $name) = $this->splitModulePath($path);
        try {
            if ($resNode = $this->getModuleNode($path, self::TAG_RESOURCE)) {
                $resNode->setAttribute('data-cms-path', $this->getModulePath($resNode));
                if ($res = Resource::getResource($this->includeDir . $module . '/' . self::DIR_RESSOURCE, $resNode)) {
                    $ret = $res->getPath();
                }
            }
        } catch(Exception $e) {
            $ret = null;
        }
        return $ret;
    }

    public function getResourceDoc($path, $loadFile = '')
    {
        list ($module, $name) = $this->splitModulePath($path);
        if (! isset($this->resourceDocs[$path])) {
            switch ($path) {
                case '/core/sites':
                    $this->resourceDocs[$path] = $this->sitesDoc;
                    break;
                default:
                    $this->resourceDocs[$path] = null;
                    if ($resNode = $this->getModuleNode($path, self::TAG_RESOURCE)) {
                        $resNode->setAttribute('data-cms-path', $this->getModulePath($resNode));
                        if ($res = Resource::getResource($this->includeDir . $module . '/' . self::DIR_RESSOURCE, $resNode, $loadFile)) {
                            $this->resourceDocs[$path] = $res->asDocument();
                        }
                    }
                    break;
            }
        }
        return $this->resourceDocs[$path];
    }

    public function setResourceDoc($path, DOMNode $resNode)
    {
        $file = $this->getResourcePath($path);
        if (! $file) {
            throw new RuntimeException('unknown path: ' . $path);
        }
        if ($resNode instanceof DOMDocument) {
            $resDoc = $resNode;
            $resNode = $resNode->documentElement->firstChild;
        } else {
            $resDoc = $resNode->ownerDocument;
        }
        $encoding = $resDoc->xmlEncoding ? $resDoc->xmlEncoding : 'UTF-8';
        $xml = sprintf('<?xml version="%s" encoding="%s"?>%s%s', $resDoc->xmlVersion, $encoding, PHP_EOL, $resDoc->saveXML($resNode));
        return file_put_contents($file, $xml);
        /*
         * $tmpDoc = new DOMDocument();
         * if ($resNode instanceof DOMDocument) {
         * $tmpDoc->appendChild($tmpDoc->importNode($resNode->documentElement->firstChild, true));
         * } else {
         * $tmpDoc->appendChild($tmpDoc->importNode($resNode, true));
         * }
         * return $tmpDoc->save($file);
         * //
         */
    }

    public function getResourceDir($path, $loadFile = '')
    {
        list ($module, $name) = $this->splitModulePath($path);
        if (! isset($this->resourceDirs[$path])) {
            $this->resourceDirs[$path] = [];
            if ($resNode = $this->getModuleNode($path, self::TAG_RESOURCEDIR)) {
                $resNode->setAttribute('data-cms-path', $this->getModulePath($resNode));
                $resArr = Resource::getResourceDir($this->includeDir . $module . '/' . self::DIR_RESSOURCE, $resNode, $loadFile);
                foreach ($resArr as $res) {
                    $this->resourceDirs[$path][$res->getName()] = $res->asDocument();
                }
            }
        }
        return $this->resourceDirs[$path];
    }

    public function getDataDoc($path)
    {
        list ($module, $name) = $this->splitModulePath($path);
        if (! isset($this->dataDocs[$path])) {
            $this->dataDocs[$path] = null;
            $file = $this->includeDir . $module . '/' . self::DIR_DATA . $name . '.php';
            if (is_file($file)) {
                // $this->dataDocs[$path] = $this->includeFile($file);
                $dataDoc = $this->impl->createDocument(null, self::TAG_DATA);
                $dataRoot = $dataDoc->documentElement;
                $dataRoot->setAttribute('path', $path);
                try {
                    $dataRes = $this->includeFile($file, $dataDoc);
                    
                    while ($dataRes instanceof HTTPClosure) {
                        if ($dataRes->isThreaded() and PHP_SAPI === 'apache2handler') {
                            // switch to CLI
                            $cmd = sprintf('php %s %s', SERVER_ROOT . 'vhosts\\cmd\\getData.php', $path);
                            $dataRes = new HTTPCommand($cmd);
                        } else {
                            $dataRes = $dataRes->run();
                        }
                    }
                } catch(Throwable $e) {
					if (headers_sent()) {
						//too late for graceful error catching...
						throw $e;
					} else {
						$dataRes = $dataDoc->createElement('error');
						$dataRes->setAttribute('type', get_class($e));
						$dataRes->setAttribute('message', $e->getMessage());
						$dataRes->setAttribute('code', $e->getCode());
						$dataRes->setAttribute('file', $e->getFile());
						$dataRes->setAttribute('line', $e->getLine());
						$dataRes->setAttribute('trace', $e->getTraceAsString());
						$dataRes->textContent = (string) $e;
					}
                }
                
                switch (true) {
                    case $dataRes instanceof HTTPFile:
                        $this->httpResponse->setFile($dataRes->getPath(), $dataRes->getName());
                        $this->progressStatus = self::STATUS_RESPONSE_SET;
                        break;
                    case $dataRes instanceof HTTPStream:
                        $this->httpResponse->setStream($dataRes);
                        $this->progressStatus = self::STATUS_RESPONSE_SET;
                        break;
                    case $dataRes instanceof HTTPCommand:
                        $this->httpResponse->setCommand($dataRes);
                        $this->progressStatus = self::STATUS_RESPONSE_SET;
                        break;
                    default:
                        $this->appendNodes($dataRoot, $dataRes);
                        if ($dataRoot->hasChildNodes()) {
                            $this->dataDocs[$path] = $dataDoc;
                        }
                        break;
                }
                // output($dataDoc);
            }
        }
        return $this->dataDocs[$path];
    }

    public function getTemplateDoc($path)
    {
        list ($module, $name) = $this->splitModulePath($path);
        if (! isset($this->templateDocs[$path])) {
            $this->templateDocs[$path] = null;
            $file = $this->includeDir . $module . '/' . self::DIR_TEMPLATE . $name . '.xsl';
            if (is_file($file)) {
                $this->templateDocs[$path] = DOMHelper::loadDocument($file);
            }
        }
        return $this->templateDocs[$path];
    }

    public function getFragmentDoc($path)
    {
        if (! isset($this->fragmentDocs[$path])) {
            if ($resNode = $this->getModuleNode($path, self::TAG_FRAGMENT)) {
                $dataDoc = $this->impl->createDocument();
                $dataRoot = $dataDoc->importNode($resNode, false);
                $dataRoot->setAttribute('path', $path);
                $dataDoc->appendChild($dataRoot);
                if ($fragment = $this->parseFragment($resNode)) {
                    $this->appendNodes($dataRoot, $fragment);
                    $templateNode = $resNode->getElementsByTagName(self::TAG_TEMPLATE_REF)->item(0);
                    $parentModule = $this->getOwnerModule($templateNode ? $templateNode : $resNode);
                    $parentModule = $this->dict->setNS($parentModule);
                    $this->dict->translateDoc($dataDoc);
                    $this->httpResponse->setLanguage($this->dict->getLang());
                    $parentModule = $this->dict->setNS($parentModule);
                }
            } else {
                $dataDoc = $this->impl->createDocument(null, 'error');
                $dataDoc->documentElement->appendChild($dataDoc->createTextNode(sprintf('Could not find module or fragment "%s"!', $path)));
            }
            $this->fragmentDocs[$path] = $dataDoc;
        }
        return $this->fragmentDocs[$path];
    }

    private function parseFragment(DOMElement $fragmentNode)
    {
        $dataDoc = $this->impl->createDocument(null, self::TAG_TRANSFORMROOT);
        $dataRoot = $dataDoc->documentElement;
        $dataRoot->appendChild($dataDoc->importNode($this->requestElement, true));
        while ($res = $this->sitesPath->evaluate(self::TAG_STRUCTURE_REF, $fragmentNode) and $res->length) {
            $strucNodes = [];
            foreach ($res as $node) {
                $strucNodes[] = $node;
            }
            foreach ($strucNodes as $parentNode) {
                $ns = $this->getOwnerModule($parentNode);
                $struc = $parentNode->getAttribute('name');
                $path = $this->canonicalizePath($struc, $ns);
                $path = implode('/', $path);
                if ($resNode = $this->getModuleNode($path, self::TAG_STRUCTURE)) {
                    foreach ($resNode->childNodes as $newNode) {
                        if ($newNode->nodeType === XML_ELEMENT_NODE) {
                            $newNode = $newNode->cloneNode(true);
                            $this->setOwnerModule($newNode, $resNode);
                            $fragmentNode->insertBefore($newNode, $parentNode);
                        }
                    }
                }
                $fragmentNode->removeChild($parentNode);
            }
        }
        $paramList = $this->parseParam($fragmentNode);
        $templateDoc = null;
        foreach ($fragmentNode->childNodes as $childNode) {
            if ($childNode->nodeType === XML_ELEMENT_NODE and $this->progressStatus & self::STATUS_CONTINUE) {				
                $this->currentElement = $childNode;
                $name = $childNode->getAttribute('name');
                $module = $this->getOwnerModule($childNode);
                $newName = $name;
                $newModule = $module;
                $path = $this->canonicalizePath($newName, $newModule);
                $path = implode('/', $path);
                $oldModule = $this->dict->setNS($newModule);
                $newDoc = null;
                switch ($childNode->tagName) {
                    case self::TAG_RESOURCE_REF:
                        $newDoc = $this->getResourceDoc($path, $childNode->getAttribute('load'));
                        break;
                    case self::TAG_RESOURCEDIR_REF:
                        $newDoc = $this->getResourceDir($path, $childNode->getAttribute('load'));
                        break;
                    case self::TAG_DATA_REF:
						$childParamList = $this->parseParam($childNode);
                        $newDoc = $this->getDataDoc($path);
						$this->resetParam($childParamList);
                        break;
                    case self::TAG_FRAGMENT:
                        $path = $this->getModulePath($childNode);
                    // my_dump($path);
                    case self::TAG_FRAGMENT_REF:
                        $newDoc = $this->getFragmentDoc($path);
                        break;
                    case self::TAG_STYLE_REF:
                        $this->httpResponse->styleFiles[$path] = $this->includeDir . $newModule . '/' . self::DIR_STYLESHEET . $newName . '.css';
                        break;
                    case self::TAG_SCRIPT_REF:
                        $this->httpResponse->scriptFiles[$path] = $this->includeDir . $newModule . '/' . self::DIR_SCRIPT . $newName . '.js';
                        break;
                    case self::TAG_TEMPLATE_REF:
                        $templateDoc = $this->getTemplateDoc($path);
                        break;
                }
                if ($newDoc) {
                    $newDocs = is_array($newDoc) ? $newDoc : [
                        $newDoc
                    ];
                    foreach ($newDocs as $newDoc) {
                        $newChild = $dataDoc->importNode($newDoc->documentElement, true);
                        $name = $childNode->hasAttribute('as') ? $childNode->getAttribute('as') : $childNode->getAttribute('name');
                        $newChild->setAttribute('data-cms-name', $name);
                        $dataRoot->appendChild($newChild);
                    }
                }
                $this->dict->setNS($oldModule);
            }
        }
        
        // $dataRoot->appendChild($dataDoc->importNode($this->requestElement, true));
        if ($templateDoc) {
            $nodeList = $templateDoc->getElementsByTagNameNS(self::NS_XSL, 'import');
            foreach ($nodeList as $node) {
                $href = $node->getAttribute('href');
                if (strpos($href, '/getTemplate.php') === 0) {
                    $xslName = substr($href, strlen('/getTemplate.php'));
                    $xslNS = '';
                    $this->canonicalizePath($xslName, $xslNS);
                    $node->setAttribute('href', '../../' . $xslNS . '/' . self::DIR_TEMPLATE . $xslName . '.xsl');
                    // my_dump($node->getAttribute('href'));
                }
            }
            $dataDoc = $this->transform($dataDoc, $templateDoc);
            if (! $dataDoc->documentElement) {
                $dataDoc = null;
            }
        }
        $this->resetParam($paramList);
        return $dataDoc;
    }

    private function parseParam(DOMElement $node)
    {
        $ret = [];
        $nodeList = $this->sitesPath->evaluate(self::TAG_PARAM, $node);
        foreach ($nodeList as $paramNode) {
            $key = $paramNode->getAttribute('name');
            $val = $paramNode->getAttribute('value');
            $scope = $paramNode->getAttribute('scope');
            
            switch ($paramNode->getAttribute('type')) {
                case 'json':
                    $val = json_decode($val, true);
                    break;
                default:
                    break;
            }
            $previousVal = null;
            switch ($scope) {
                case 'global':
                    $previousVal = $val;
                    break;
                default:
                    if (isset($this->httpRequest->input[$key])) {
                        $previousVal = $this->httpRequest->input[$key];
                    }
                    break;
            }
            $ret[$key] = $previousVal;
            if (isset($this->httpRequest->input[$key])) {
                if (is_array($val)) {
                    $this->httpRequest->input[$key] = array_merge((array) $val, (array) $this->httpRequest->input[$key]);
                } else {
					$this->httpRequest->input[$key] = $val;
				}
            } else {
                $this->httpRequest->input[$key] = $val;
            }
            $_REQUEST[$key] = $val;
            $this->dataDocs = [];
            $this->fragmentDocs = [];
        }
        return $ret;
    }

    private function resetParam(array $paramList)
    {
        foreach ($paramList as $key => $val) {
            $this->httpRequest->input[$key] = $val;
            $_REQUEST[$key] = $val;
        }
    }
}


