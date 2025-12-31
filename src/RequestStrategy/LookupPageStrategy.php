<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\RequestStrategy;

use Psr\Http\Message\ServerRequestInterface;
use Slothsoft\Farah\Exception\HttpStatusException;
use Slothsoft\Farah\Exception\PageNotFoundException;
use Slothsoft\Farah\Exception\PageRedirectionException;
use Slothsoft\Farah\FarahUrl\FarahUrl;
use Slothsoft\Farah\Http\StatusCode;
use Slothsoft\Farah\Sites\Domain;
use DOMElement;

final class LookupPageStrategy extends RequestStrategyBase {
    
    private ?Domain $domain;
    
    public function __construct(?Domain $domain = null) {
        $this->domain = $domain;
    }
    
    public function createUrl(ServerRequestInterface $request): FarahUrl {
        $this->domain ??= Domain::createWithDefaultSitemap();
        
        $uri = $request->getUri();
        $body = $request->getParsedBody();
        $params = $request->getQueryParams();
        
        if (is_array($body)) {
            $args = $body + $params;
        } else {
            $args = $params;
        }
        
        try {
            $path = urldecode($uri->getPath());
            $pageNode = $this->lookupPageNode($path);
        } catch (PageRedirectionException $e) {
            $url = $e->getTargetPath();
            if (count($args)) {
                $url .= '?' . http_build_query($args);
            }
            throw new HttpStatusException($e->getMessage(), StatusCode::STATUS_PERMANENT_REDIRECT, $e, [
                'location' => $url
            ]);
        } catch (PageNotFoundException $e) {
            throw new HttpStatusException($e->getMessage(), StatusCode::STATUS_GONE, $e);
        }
        
        $this->domain->setCurrentPageNode($pageNode);
        
        if (! $pageNode->hasAttribute(Domain::ATTR_REFERENCE)) {
            throw new HttpStatusException("The URL $uri does not contain an asset.\n{$pageNode->ownerDocument->saveXML($pageNode)}", StatusCode::STATUS_NOT_IMPLEMENTED);
        }
        
        $url = $this->domain->lookupAssetUrl($pageNode, $args);
        
        return $url;
    }
    
    private function lookupPageNode(string $path, DOMElement $contextNode = null): DOMElement {
        if ($path === '') {
            $path = '/';
        }
        
        if ($contextNode === null or $path[0] === '/') {
            $contextNode = $this->domain->getDomainNode();
        }
        
        foreach (explode('/', strtolower($path)) as $segment) {
            switch ($segment) {
                case '':
                case '.':
                    break;
                case '..':
                    $contextNode = $contextNode->parentNode;
                    break;
                default:
                    /** @var $node DOMElement */
                    foreach ($contextNode->childNodes as $node) {
                        if ($node->nodeType !== XML_ELEMENT_NODE) {
                            continue;
                        }
                        
                        switch ($node->localName) {
                            case Domain::TAG_PAGE:
                            case Domain::TAG_FILE:
                                $pageName = strtolower((string) $node->getAttribute(Domain::ATTR_NAME));
                                if ($pageName === $segment) {
                                    $contextNode = $node;
                                    break 3;
                                }
                                
                                break;
                        }
                    }
                    
                    throw new PageNotFoundException($path);
            }
        }
        
        if ($contextNode->hasAttribute(Domain::ATTR_REDIRECT)) {
            $redirectPath = $contextNode->getAttribute(Domain::ATTR_REDIRECT);
            $host = parse_url($redirectPath, PHP_URL_HOST);
            if ($host) {
                throw new PageRedirectionException($redirectPath);
            }
            $redirectNode = $this->lookupPageNode($redirectPath, $contextNode);
            throw new PageRedirectionException($redirectNode->getAttribute(Domain::ATTR_URI));
        }
        
        if ($contextNode->getAttribute(Domain::ATTR_URI) !== $path) {
            throw new PageRedirectionException($contextNode->getAttribute(Domain::ATTR_URI));
        }
        
        return $contextNode;
    }
}

