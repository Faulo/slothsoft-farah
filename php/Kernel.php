<?php declare(strict_types=1);
/***********************************************************************
 * Slothsoft\Farah\Kernel v1.00 19.10.2012 © Daniel Schulz
 * 
 * 	Changelog:
 *		v1.00 19.10.2012
 *			initial release
 ***********************************************************************/
namespace Slothsoft\Farah;

use Slothsoft\Core\DOMHelper;
use Slothsoft\Farah\Event\EventTargetInterface;
use Slothsoft\Farah\Event\EventTargetTrait;
use Slothsoft\Farah\Event\Events\EventInterface;
use Slothsoft\Farah\Event\Events\SetParameterEvent;
use Slothsoft\Farah\Event\Events\UseAssetEvent;
use Slothsoft\Farah\Exception\ExceptionContext;
use Slothsoft\Farah\Module\AssetRepository;
use Slothsoft\Farah\Module\FarahUrl;
use Slothsoft\Farah\Module\Module;
use Slothsoft\Farah\Module\AssetUses\DOMWriterInterface;
use Slothsoft\Farah\Module\AssetUses\FileWriterInterface;
use Slothsoft\Farah\Module\Assets\AssetInterface;
use Slothsoft\Farah\Module\Assets\Fragment;
use Slothsoft\Farah\Sites\Domain;
use Slothsoft\Farah\Tracking\Manager;
use DOMDocument;
use DomainException;
use LogicException;
use Throwable;


class Kernel implements EventTargetInterface
{
    use EventTargetTrait;

    const LOOKUP_PAGE = 'page';

    const LOOKUP_ASSET = 'asset';

    const STATUS_CONTINUE = 1;

    const STATUS_RESPONSE_SET = 2;

    const ERR_REQRES = 'Rendering of this page took %d ms and %.2f MB.';

    protected static $lookupURIList = [
        self::LOOKUP_PAGE => '/getPage.php',
        self::LOOKUP_ASSET => '/getAsset.php'
    ];

    /**
     *
     * @return NULL|\Slothsoft\Farah\Kernel
     */
    public static function getInstance(): Kernel
    {
        static $singleton;
        if (! $singleton) {
            $singleton = new Kernel();
        }
        return $singleton;
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
        
        $httpDocument = self::getInstance();
        $httpDocument->init(SERVER_ROOT . FILE_SITEMAP);
        
        $response = $httpDocument->lookup($request);
        
        if (constant('CMS_TRACKING_ENABLED')) {
            $track = ! $request->hasInputValue('dnt');
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
                } catch (Throwable $e) {}
            }
        }
        
        return $response;
    }

    public static function createRequestURI($path, $mode, array $req = null)
    {
        if (! isset(self::$lookupURIList[$mode])) {
            throw new DomainException("Lookup mode '$mode' is not supported by this implementation.");
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

    private $httpRequest;

    private $httpResponse;

    private $domain;

    private $dict;

    private $now;

    private $progressStatus = self::STATUS_CONTINUE;

    private $redirectUp = false;

    private $redirectUpAgents = [
        'Googlebot',
        'bingbot'
    ];

    private function __construct()
    {
        $this->addEventListener(Module::EVENT_USE_DOCUMENT, function (EventInterface $event) {
            if ($event instanceof UseAssetEvent) {
                // echo "kernel: using asset " . $event->getAsset()->getId() . PHP_EOL;
            }
        });
    }

    public function init($siteMapPath)
    {
        $this->domain = new Domain($siteMapPath);
        // $this->domain->init();
    }

    public function getRequest(): HTTPRequest
    {
        return $this->httpRequest;
    }

    public function getResponse(): HTTPResponse
    {
        return $this->httpResponse;
    }

    public function getSitesDocument(): DOMDocument
    {
        return $this->domain->getDocument();
    }

    public function getDefaultVendor(): string
    {
        return 'slothsoft'; // TODO
    }

    public function getDefaultModule(): string
    {
        return 'farah'; // TODO
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
        
        // $this->lookupDomain($this->httpRequest->clientHost);
        
        if ($this->isBanned($this->httpRequest->clientIp)) {
            // BANHAMMER
            $this->httpResponse->setStatus(HTTPResponse::STATUS_PRECONDITION_FAILED, 'You have been found wanting.');
            return $this->httpResponse;
        }
        
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
                                
                                try {
                                    switch ($this->httpRequest->mode) {
                                        case self::LOOKUP_PAGE:
                                            $ret = $this->lookupPage();
                                            break;
                                        case self::LOOKUP_ASSET:
                                            $ret = $this->lookupAsset();
                                            break;
                                        default:
                                            throw new DomainException("Lookup mode '$this->httpRequest->mode' is not supported by this implementation.");
                                    }
                                } catch (Throwable $exception) {
                                    $ret = $exception;
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
                                        case $ret instanceof Throwable:
                                            $this->httpResponse->setExceptionContext(ExceptionContext::append($ret)->exceptionContext);
                                            $this->progressStatus |= self::STATUS_RESPONSE_SET;
                                            break;
                                        case is_string($ret) and is_file($ret):
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
        
        return $this->httpResponse;
    }

    private function lookupPage()
    {
        $pageNode = $this->domain->lookupPageNode($this->httpRequest->path);
        
        if ($pageNode) {
            $pageNode->setAttribute('current', '1');
            
            if ($pageNode->hasAttribute('ref')) {
                $this->httpResponse->addHeader('content-location', $pageNode->getAttribute('url'));
                
                $url = $this->domain->lookupAssetUrl($pageNode);
                $asset = AssetRepository::getInstance()->lookupAssetByUrl($url);
                
                // echo "loaded page {$asset->getId()}, processing..." . PHP_EOL;
                try {
                    return $this->loadAsset($asset);
                } catch (Throwable $exception) {
                    throw ExceptionContext::append($exception, [
                        'asset' => $asset,
                        'definition' => $asset->getDefinition()
                    ]);
                }
            }
        }
    }

    private function lookupAsset()
    {
        $ref = $this->httpRequest->path;
        $args = $this->httpRequest->input;
        
        $repository = AssetRepository::getInstance();
        $module = $repository->lookupModule($this->getDefaultVendor(), $this->getDefaultModule());
        $url = FarahUrl::createFromReference($ref, $module, $args);
        $asset = $repository->lookupAssetByUrl($url);
        
        try {
            return $this->loadAsset($asset);
        } catch (Throwable $exception) {
            throw ExceptionContext::append($exception, [
                'asset' => $asset,
                'definition' => $asset->getDefinition()
            ]);
        }
    }

    private function loadAsset(AssetInterface $asset)
    {
        switch (true) {
            case $asset instanceof Fragment:
                $stylesheetList = [];
                $scriptList = [];
                $this->addEventListener(Module::EVENT_USE_STYLESHEET, function (EventInterface $event) use (&$stylesheetList) {
                    if ($event instanceof UseAssetEvent) {
                        $stylesheetList[$event->getAsset()
                            ->getId()] = null;
                    }
                });
                $this->addEventListener(Module::EVENT_USE_SCRIPT, function (EventInterface $event) use (&$scriptList) {
                    if ($event instanceof UseAssetEvent) {
                        $scriptList[$event->getAsset()
                            ->getId()] = null;
                    }
                });
                
                $this->addEventListener(Module::EVENT_SET_PARAMETER, function (EventInterface $event) use (&$scriptList) {
                    if ($event instanceof SetParameterEvent) {
                        // echo "setting '{$event->getName()}' to '{$event->getValue()}'" . PHP_EOL;
                        $this->httpRequest->setInputValue($event->getName(), $event->getValue());
                    }
                });
                
                $document = $asset->toDocument();
                if ($stylesheetList or $scriptList) {
                    $rootNode = null;
                    $parentNode = $document->createDocumentFragment();
                    $ns = $document->documentElement->namespaceURI;
                    switch ($ns) {
                        case DOMHelper::NS_HTML:
                            $rootNode = $document->getElementsByTagNameNS(DOMHelper::NS_HTML, 'head')->item(0);
                            foreach ($stylesheetList as $assetId => $asset) {
                                $assetLink = str_replace('farah://', '/getAsset.php/', $assetId);
                                $node = $document->createElementNS(DOMHelper::NS_HTML, 'link');
                                $node->setAttribute('href', $assetLink);
                                $node->setAttribute('rel', 'stylesheet');
                                $node->setAttribute('type', 'text/css');
                                $parentNode->appendChild($node);
                            }
                            foreach ($scriptList as $assetId => $asset) {
                                $assetLink = str_replace('farah://', '/getAsset.php/', $assetId);
                                $node = $document->createElementNS(DOMHelper::NS_HTML, 'script');
                                $node->setAttribute('src', $assetLink);
                                $node->setAttribute('defer', 'defer');
                                $node->setAttribute('type', 'application/javascript');
                                $parentNode->appendChild($node);
                            }
                            break;
                        default:
                            throw new DomainException("This implementation does not support <sfm:use-stylesheet> and <sfm:use-script> for XML namespace '$ns'.");
                    }
                    if (! $rootNode) {
                        $rootNode = $document->documentElement;
                    }
                    $rootNode->appendChild($parentNode);
                }
                return $document;
                break;
            case $asset instanceof FileWriterInterface:
                return $asset->toFile();
            case $asset instanceof DOMWriterInterface:
                return $asset->toDocument();
            default:
                throw new LogicException("To load asset {$asset->getId()}, it must implement a Writer-type thingamabob!");
        }
    }
}


