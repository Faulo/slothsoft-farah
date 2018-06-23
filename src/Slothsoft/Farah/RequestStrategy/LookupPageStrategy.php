<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\RequestStrategy;

use Psr\Http\Message\ServerRequestInterface;
use Slothsoft\Farah\Kernel;
use Slothsoft\Farah\Exception\HttpStatusException;
use Slothsoft\Farah\Exception\PageNotFoundException;
use Slothsoft\Farah\Exception\PageRedirectionException;
use Slothsoft\Farah\FarahUrl\FarahUrl;
use Slothsoft\Farah\Http\StatusCode;
use Slothsoft\Farah\Sites\Domain;

class LookupPageStrategy extends RequestStrategyBase
{

    private $domain;

    public function __construct()
    {
        $this->domain = new Domain(Kernel::getCurrentSitemap());
    }

    protected function createUrl(ServerRequestInterface $request): FarahUrl
    {
        $uri = $request->getUri();
        $body = $request->getParsedBody();
        $params = $request->getQueryParams();
        
        if (is_array($body)) {
            $args = $body + $params;
        } else {
            $args = $params;
        }
        
        try {
            $pageNode = $this->domain->lookupPageNode($uri->getPath());
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
        
        $pageNode->setAttribute('current', '1');
        
        if (! $pageNode->hasAttribute('ref')) {
            throw new HttpStatusException("The URL $uri does not contain an asset.\n{$pageNode->ownerDocument->saveXML($pageNode)}", StatusCode::STATUS_NOT_IMPLEMENTED);
        }
        
        $url = $this->domain->lookupAssetUrl($pageNode, $args);
        
        return $url;
    }
}

