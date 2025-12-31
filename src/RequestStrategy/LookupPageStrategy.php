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
            $pageNode = $this->lookupPageNode(urldecode($uri->getPath()));
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
        $path = str_pad($path, 1, '/');
        if ($contextNode === null or $path[0] === '/') {
            $contextNode = $this->domain->getDomainNode();
        }
        $query = $this->path2expr($path, '[self::sfs:page | self::sfs:file]');
        $pageNode = $this->domain->getXPath()
            ->evaluate($query, $contextNode)
            ->item(0);
        
        if (! $pageNode) {
            throw new PageNotFoundException($path);
        }
        
        if ($pageNode->hasAttribute(Domain::ATTR_REDIRECT)) {
            $redirectPath = $pageNode->getAttribute(Domain::ATTR_REDIRECT);
            
            $host = parse_url($redirectPath, PHP_URL_HOST);
            if ($host) {
                throw new PageRedirectionException($redirectPath);
            }
            
            switch ($redirectPath) {
                case '..':
                    $redirectPath = $pageNode->parentNode->getAttribute(Domain::ATTR_URI);
                    break;
            }
            
            $redirectNode = $this->lookupPageNode($redirectPath, $pageNode);
            throw new PageRedirectionException($redirectNode->getAttribute(Domain::ATTR_URI));
        }
        
        if ($pageNode->getAttribute(Domain::ATTR_URI) !== $path) {
            throw new PageRedirectionException($pageNode->getAttribute(Domain::ATTR_URI));
        }
        
        return $pageNode;
    }
    
    private function path2expr(string $path, string $filter = ''): string {
        $path = array_filter(explode('/', $path), 'strlen');
        $qry = [
            '.'
        ];
        foreach ($path as $folder) {
            $qry[] = '*[php:functionString("strtolower", @name) = "' . strtolower($folder) . '" or ancestor-or-self::*/@name = "*"]';
        }
        return implode('/', $qry);
    }
}

