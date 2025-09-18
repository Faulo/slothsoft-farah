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

class LookupPageStrategy extends RequestStrategyBase {
    
    private ?Domain $domain = null;
    
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
            $pageNode = $this->domain->lookupPageNode(urldecode($uri->getPath()));
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
        
        if (! $pageNode->hasAttribute('ref')) {
            throw new HttpStatusException("The URL $uri does not contain an asset.\n{$pageNode->ownerDocument->saveXML($pageNode)}", StatusCode::STATUS_NOT_IMPLEMENTED);
        }
        
        $url = $this->domain->lookupAssetUrl($pageNode, $args);
        
        return $url;
    }
}

