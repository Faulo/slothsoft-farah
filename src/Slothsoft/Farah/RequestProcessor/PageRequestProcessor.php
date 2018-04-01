<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\RequestProcessor;

use Slothsoft\Farah\Module\FarahUrl\FarahUrlResolver;
use Slothsoft\Farah\Module\Results\ResultInterface;
use Slothsoft\Farah\Sites\Domain;
use Slothsoft\Farah\Exception\PageNotFoundException;
use Slothsoft\Farah\Exception\HttpStatusException;
use Slothsoft\Farah\HTTPResponse;

class PageRequestProcessor extends RequestProcessorImplementation
{

    protected function loadResult(): ResultInterface
    {
        $ref = $this->request->path;
        $args = $this->request->input;
        
        $domain = Domain::getInstance();
        
        try {
            $pageNode = $domain->lookupPageNode($ref);
        } catch(PageNotFoundException $e) {
            throw new HttpStatusException($e->getMessage(), HTTPResponse::STATUS_GONE, $e);
        }
        
        $pageNode->setAttribute('current', '1');
        
        if ($pageNode->hasAttribute('ref')) {
            $this->response->addHeader('content-location', $pageNode->getAttribute('url'));
            
            $url = $domain->lookupAssetUrl($pageNode, $args);
            
            // echo "determined page url {$url}, processing..." . PHP_EOL;
            
            return FarahUrlResolver::resolveToResult($url);
        }
    }
}

