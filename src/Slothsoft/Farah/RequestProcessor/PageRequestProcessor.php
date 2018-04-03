<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\RequestProcessor;

use Slothsoft\Farah\HTTPResponse;
use Slothsoft\Farah\Exception\AssetPathNotFoundException;
use Slothsoft\Farah\Exception\HttpStatusException;
use Slothsoft\Farah\Exception\ModuleNotFoundException;
use Slothsoft\Farah\Exception\PageNotFoundException;
use Slothsoft\Farah\Module\FarahUrl\FarahUrlResolver;
use Slothsoft\Farah\Module\Results\ResultInterface;
use Slothsoft\Farah\Sites\Domain;
use Slothsoft\Farah\Exception\PageRedirectionException;

class PageRequestProcessor extends RequestProcessorImplementation
{

    protected function loadResult(): ResultInterface
    {
        $ref = $this->request->path;
        $args = $this->request->input;
        
        $domain = Domain::getInstance();
        
        try {
            $pageNode = $domain->lookupPageNode($ref);
        } catch (PageRedirectionException $e) {
            $this->response->addHeader('location', $e->getTargetPath());
            throw new HttpStatusException($e->getMessage(), HTTPResponse::STATUS_PERMANENT_REDIRECT, $e);
        } catch (PageNotFoundException $e) {
            throw new HttpStatusException($e->getMessage(), HTTPResponse::STATUS_GONE, $e);
        }
        
        $pageNode->setAttribute('current', '1');
        
        if (! $pageNode->hasAttribute('ref')) {
            throw new HttpStatusException('', HTTPResponse::STATUS_NOT_IMPLEMENTED);
        }
        
        $this->response->addHeader('content-location', $pageNode->getAttribute('url'));
        
        $url = $domain->lookupAssetUrl($pageNode, $args);
        
        // echo "determined page url {$url}, processing..." . PHP_EOL;
        
        try {
            return FarahUrlResolver::resolveToResult($url);
        } catch (ModuleNotFoundException $e) {
            throw new HttpStatusException($e->getMessage(), HTTPResponse::STATUS_INTERNAL_SERVER_ERROR, $e);
        } catch (AssetPathNotFoundException $e) {
            throw new HttpStatusException($e->getMessage(), HTTPResponse::STATUS_INTERNAL_SERVER_ERROR, $e);
        }
    }
}

