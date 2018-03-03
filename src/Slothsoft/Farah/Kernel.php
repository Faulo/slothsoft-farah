<?php
declare(strict_types = 1);
/**
 * *********************************************************************
 * Slothsoft\Farah\Kernel v1.00 19.10.2012 © Daniel Schulz
 *
 * Changelog:
 * v1.00 19.10.2012
 * initial release
 * *********************************************************************
 */
namespace Slothsoft\Farah;

use Slothsoft\Farah\Event\EventTargetInterface;
use Slothsoft\Farah\Event\EventTargetTrait;
use Slothsoft\Farah\Event\Events\EventInterface;
use Slothsoft\Farah\Event\Events\SetParameterEvent;
use Slothsoft\Farah\Event\Events\UseAssetEvent;
use Slothsoft\Farah\Exception\ExceptionContext;
use Slothsoft\Farah\LinkDecorator\DecoratorFactory;
use Slothsoft\Farah\Module\Module;
use Slothsoft\Farah\Module\FarahUrl\FarahUrl;
use Slothsoft\Farah\Module\FarahUrl\FarahUrlArguments;
use Slothsoft\Farah\Module\FarahUrl\FarahUrlAuthority;
use Slothsoft\Farah\Module\FarahUrl\FarahUrlResolver;
use Slothsoft\Farah\Module\Results\FragmentResult;
use Slothsoft\Farah\Module\Results\ResultInterface;
use Slothsoft\Farah\Sites\Domain;
use Slothsoft\Farah\Tracking\Manager;
use DOMDocument;
use DomainException;
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
        static $instance;
        if ($instance === null) {
            $instance = new Kernel();
        }
        return $instance;
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

    private $linkedAssetCollector;

    private $progressStatus = self::STATUS_CONTINUE;

    private $redirectUp = false;

    private $redirectUpAgents = [
        'Googlebot',
        'bingbot'
    ];

    private function __construct()
    {
        $this->linkedAssetCollector = new LinkedAssetCollector();
        $this->addEventListener(Module::EVENT_USE_DOCUMENT, function (EventInterface $event) {
            if ($event instanceof UseAssetEvent) {
                // echo "kernel: using asset " . $event->getAsset()->getId() . PHP_EOL;
            }
        });
        $this->addEventListener(Module::EVENT_SET_PARAMETER, function (EventInterface $event) use (&$scriptList) {
            if ($event instanceof SetParameterEvent) {
                // echo "setting '{$event->getName()}' to '{$event->getValue()}'" . PHP_EOL;
                $this->httpRequest->setInputValue($event->getName(), $event->getValue());
            }
        });
        $this->addEventListener(Module::EVENT_USE_STYLESHEET, [
            $this->linkedAssetCollector,
            'onStylesheet'
        ]);
        $this->addEventListener(Module::EVENT_USE_STYLESHEET, [
            $this->linkedAssetCollector,
            'onScript'
        ]);
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
                                        case $ret instanceof FragmentResult:
                                            $ret = $ret->toDocument();
                                            if ($ret->documentElement) {
                                                $decorator = DecoratorFactory::createForNamespace((string) $ret->documentElement->namespaceURI);
                                                $decorator->decorateDocument($ret, $this->linkedAssetCollector->getStylesheetList(), $this->linkedAssetCollector->getScriptList());
                                            }
                                            $this->httpResponse->setDocument($ret);
                                            $this->progressStatus |= self::STATUS_RESPONSE_SET;
                                            break;
                                        case $ret instanceof DOMDocument:
                                            $this->httpResponse->setDocument($ret);
                                            $this->progressStatus |= self::STATUS_RESPONSE_SET;
                                            break;
                                        case $ret instanceof ResultInterface:
                                            $ret = $ret->toFile();
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

    private function lookupPage(): ResultInterface
    {
        $pageNode = $this->domain->lookupPageNode($this->httpRequest->path);
        
        if ($pageNode) {
            $pageNode->setAttribute('current', '1');
            
            if ($pageNode->hasAttribute('ref')) {
                $this->httpResponse->addHeader('content-location', $pageNode->getAttribute('url'));
                
                $url = $this->domain->lookupAssetUrl($pageNode);
                
                // echo "determined page url {$url}, processing..." . PHP_EOL;
                
                return FarahUrlResolver::resolveToResult($url);
            }
        }
    }

    private function lookupAsset(): ResultInterface
    {
        $ref = $this->httpRequest->path;
        $args = $this->httpRequest->input;
        
        $url = FarahUrl::createFromReference($ref, FarahUrlAuthority::createFromVendorAndModule($this->getDefaultVendor(), $this->getDefaultModule()), null, FarahUrlArguments::createFromValueList($args));
        // echo "determined asset url {$url}, processing..." . PHP_EOL;
        return FarahUrlResolver::resolveToResult($url);
    }
}


