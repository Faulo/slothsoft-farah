<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\RequestProcessor;

use Slothsoft\Farah\Module\FarahUrl\FarahUrlResolver;
use Slothsoft\Farah\Module\Results\ResultInterface;
use Slothsoft\Farah\Sites\Domain;

class PageRequestProcessor extends RequestProcessorImplementation
{

    protected function loadResult(): ResultInterface
    {
        $ref = $this->request->path;
        $args = $this->request->input;
        
        $domain = Domain::getInstance();
        
        if ($pageNode = $domain->lookupPageNode($ref)) {
            $pageNode->setAttribute('current', '1');
            
            if ($pageNode->hasAttribute('ref')) {
                $this->response->addHeader('content-location', $pageNode->getAttribute('url'));
                
                $url = $domain->lookupAssetUrl($pageNode, $args);
                
                // echo "determined page url {$url}, processing..." . PHP_EOL;
                
                return FarahUrlResolver::resolveToResult($url);
            }
        }
    }
}

