<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\RequestStrategy;

use Laminas\Http\Request;
use Laminas\Router\Http\RouteMatch;
use Laminas\Router\Http\TreeRouteStack;
use Psr\Http\Message\ServerRequestInterface;
use Slothsoft\Farah\FarahUrl\FarahUrl;
use Slothsoft\Farah\FarahUrl\FarahUrlArguments;
use Slothsoft\Farah\FarahUrl\FarahUrlAuthority;
use Slothsoft\Farah\Module\Manifest\Manifest;
use Slothsoft\Farah\Sites\Domain;
use DOMElement;

final class LookupRouteStrategy extends RequestStrategyBase {
    
    private ?Domain $domain = null;
    
    public function createUrl(ServerRequestInterface $serverRequest): FarahUrl {
        $this->domain ??= Domain::createWithDefaultSitemap();
        
        $uri = $serverRequest->getUri();
        $body = $serverRequest->getParsedBody();
        $params = $serverRequest->getQueryParams();
        
        $stack = new TreeRouteStack();
        $stack->addRoutes($this->domain->toRoutes());
        $zendRequest = new Request();
        $zendRequest->setUri((string) $uri);
        $match = $stack->match($zendRequest);
        // print_r($this->domain->toRoutes());
        var_dump([
            (string) $uri => $match
        ]);
        die();
        
        if (is_array($body)) {
            $args = $body + $params;
        } else {
            $args = $params;
        }
        
        if ($uri instanceof FarahUrl) {
            $url = $uri;
        } else {
            $urlAuthority = FarahUrlAuthority::createFromVendorAndModule(self::DEFAULT_VENDOR, self::DEFAULT_MODULE);
            $urlPath = null;
            $urlArgs = FarahUrlArguments::createFromValueList($args);
            
            $url = FarahUrl::createFromReference($this->extractFarahUrl($uri->getPath()), FarahUrl::createFromComponents($urlAuthority, $urlPath, $urlArgs));
        }
        
        return $url;
    }
    
    private function getDomainName(): string {
        return $this->getDomainNode()->getAttribute('name');
    }
    
    private function getProtocol(): string {
        return 'http'; // TODO: where to put this?
    }
    
    public function toRoutes(): array {
        $this->loadDocument();
        return [
            $this->getDomainName() => $this->toRoute($this->getDomainNode(), FarahUrl::createFromReference('farah://slothsoft@farah/'))
        ];
    }
    
    private function toRoute(DOMElement $pageNode, FarahUrl $contextUrl): array {
        if ($pageNode->hasAttribute('module')) {
            $module = $pageNode->getAttribute('module');
            if ($pageNode->hasAttribute('vendor')) {
                $vendor = $pageNode->getAttribute('vendor');
            } else {
                $vendor = $contextUrl->getUserInfo();
            }
            $contextUrl = $contextUrl->withAssetAuthority(FarahUrlAuthority::createFromVendorAndModule($vendor, $module));
        }
        switch ($pageNode->localName) {
            case self::TAG_DOMAIN:
                $pageName = '/';
                break;
            case self::TAG_PAGE:
                $pageName = $pageNode->getAttribute('name') . '/';
            case self::TAG_FILE:
                $pageName = $pageNode->getAttribute('name');
                break;
        }
        
        $children = [];
        $params = [];
        foreach ($pageNode->childNodes as $node) {
            if ($node->nodeType === XML_ELEMENT_NODE) {
                switch ($node->localName) {
                    case Manifest::TAG_PARAM:
                        $params[$node->getAttribute('name')] = $node->getAttribute('value');
                        break;
                    case self::TAG_PAGE:
                    case self::TAG_FILE:
                        $children[] = $this->toRoute($node, $contextUrl);
                        break;
                }
            }
        }
        $route = [
            'type' => 'literal',
            'options' => [
                'route' => $pageName,
                'defaults' => $params
            ],
            'child_routes' => $children
        ];
        
        if ($pageNode->hasAttribute('ref')) {
            $route['may_terminate'] = true;
            $ref = $pageNode->getAttribute('ref');
            $route['options']['defaults']['asset'] = (string) FarahUrl::createFromReference($ref, $contextUrl);
        }
        RouteMatch::class;
        return $route;
    }
}

