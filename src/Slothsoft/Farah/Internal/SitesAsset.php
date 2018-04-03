<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Internal;

use Slothsoft\Core\DOMHelper;
use Slothsoft\Core\Configuration\ConfigurationRequiredException;
use Slothsoft\Farah\Module\FarahUrl\FarahUrl;
use Slothsoft\Farah\Module\Node\Asset\AssetImplementation;
use Slothsoft\Farah\Module\Results\ResultCatalog;
use Slothsoft\Farah\Module\Results\ResultInterface;
use Slothsoft\Farah\Sites\Domain;
use DOMDocument;

/**
 *
 * @author Daniel Schulz
 *        
 */
class SitesAsset extends AssetImplementation
{

    protected function loadResult(FarahUrl $url): ResultInterface
    {
        try {
            $document = Domain::getInstance()->getDocument();
        } catch (ConfigurationRequiredException $e) {
            $document = new DOMDocument();
            $document->appendChild($document->createElementNS(DOMHelper::NS_FARAH_SITES, Domain::TAG_SITEMAP));
            $document->documentElement->setAttribute('version', '1.0');
        }
        return ResultCatalog::createDOMDocumentResult($url, $document);
    }
}

