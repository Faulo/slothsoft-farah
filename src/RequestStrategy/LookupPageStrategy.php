<?php
declare(strict_types = 1);

namespace Slothsoft\Farah\RequestStrategy;

use DOMElement;
use Psr\Http\Message\ServerRequestInterface;
use Slothsoft\Farah\Exception\HttpStatusException;
use Slothsoft\Farah\Exception\PageNotFoundException;
use Slothsoft\Farah\Exception\PageRedirectionException;
use Slothsoft\Farah\FarahUrl\FarahUrl;
use Slothsoft\Farah\FarahUrl\FarahUrlStreamIdentifier;
use Slothsoft\Farah\Http\StatusCode;
use Slothsoft\Farah\Http\WebScheme;
use Slothsoft\Farah\Kernel;
use Slothsoft\Farah\Sites\Domain;

/**
 * Request strategy that resolves a sitemap page request to its target Farah asset URL.
 *
 * @author Daniel Schulz
 * @since 2018-04-17
 */
final class LookupPageStrategy extends RequestStrategyBase {
    
    private ?Domain $domain;

    private ?FarahUrlStreamIdentifier $defaultStream;
    
    private bool $usesDefaultDomain;
    
    private ?string $domainScheme = null;
    
    public function __construct(?Domain $domain = null, ?FarahUrlStreamIdentifier $defaultStream = null) {
        $this->domain = $domain;
        $this->defaultStream = $defaultStream;
        $this->usesDefaultDomain = $domain === null;
    }
    
    public function createUrl(ServerRequestInterface $request): FarahUrl {
        Kernel::setCurrentRequest($request);
        $uri = $request->getUri();
        if ($this->usesDefaultDomain and $this->domainScheme !== $uri->getScheme()) {
            $scheme = WebScheme::isSupported($uri->getScheme()) ? WebScheme::normalize($uri->getScheme()) : WebScheme::HTTP;
            $this->domain = Domain::createWithDefaultSitemap($scheme);
            $this->domainScheme = $uri->getScheme();
        }

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
        if ($url->getStreamIdentifier() === FarahUrlStreamIdentifier::createEmpty() and $this->defaultStream !== null) {
            $url = $url->withStreamIdentifier($this->defaultStream);
        }
        return $url;
    }
    
    public function lookupPageNode(string $path, ?DOMElement $contextNode = null): DOMElement {
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
