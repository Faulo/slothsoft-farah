<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Node\Asset;

use Slothsoft\Core\Storage;
use Slothsoft\Core\Calendar\Seconds;
use Slothsoft\Farah\Module\Module;
use Slothsoft\Farah\Module\FarahUrl\FarahUrl;
use Slothsoft\Farah\Module\Results\DOMDocumentResult;
use Slothsoft\Farah\Module\Results\ResultInterface;

/**
 *
 * @author Daniel Schulz
 *        
 */
class ExternalDocumentAsset extends AssetImplementation
{
    public function getHref(): string
    {
        return $this->getElementAttribute(Module::ATTR_HREF);
    }
    
    protected function loadResult(FarahUrl $url): ResultInterface
    {
        $document = Storage::loadExternalDocument($this->getHref(), Seconds::DAY);
        return new DOMDocumentResult($url, $document);
    }
}

