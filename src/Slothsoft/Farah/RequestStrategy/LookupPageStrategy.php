<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\RequestStrategy;

use Psr\Http\Message\ServerRequestInterface;
use Slothsoft\Farah\Exception\HttpStatusException;
use Slothsoft\Farah\Exception\PageNotFoundException;
use Slothsoft\Farah\Exception\PageRedirectionException;
use Slothsoft\Farah\Http\StatusCodes;
use Slothsoft\Farah\Module\FarahUrl\FarahUrl;
use Slothsoft\Farah\Sites\Domain;

class LookupPageStrategy extends RequestStrategyBase
{

    protected function createUrl(ServerRequestInterface $request): FarahUrl
    {
        $uri = $request->getUri();
        $args = $request->getQueryParams();
        
        $domain = Domain::getInstance();
        
        try {
            $pageNode = $domain->lookupPageNode($uri->getPath());
        } catch (PageRedirectionException $e) {
            $url = $e->getTargetPath();
            if (count($args)) {
                $url .= '?' . http_build_query($args);
            }
            throw new HttpStatusException($e->getMessage(), StatusCodes::STATUS_PERMANENT_REDIRECT, $e, ['location' => $url]);
        } catch (PageNotFoundException $e) {
            throw new HttpStatusException($e->getMessage(), StatusCodes::STATUS_GONE, $e);
        }
        
        $pageNode->setAttribute('current', '1');
        
        if (! $pageNode->hasAttribute('ref')) {
            throw new HttpStatusException('', StatusCodes::STATUS_NOT_IMPLEMENTED);
        }
        
        $url = $domain->lookupAssetUrl($pageNode, $args);
        
        return $url;
    }
}

