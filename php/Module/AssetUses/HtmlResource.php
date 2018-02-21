<?php declare(strict_types=1);
namespace Slothsoft\Farah\Module\Assets\Resources;

use Slothsoft\Core\DOMHelper;
use Slothsoft\Farah\Module\AssetUses\DOMWriterInterface;
use Slothsoft\Farah\Module\Assets\Resource;
use DOMDocument;
use DOMElement;

/**
 *
 * @author Daniel Schulz
 *        
 */
class HtmlResource extends Resource implements DOMWriterInterface
{

    public function toElement(DOMDocument $targetDoc): DOMElement
    {
        return $targetDoc->importNode($this->toDocument()->documentElement, true);
    }

    public function toDocument(): DOMDocument
    {
        return DOMHelper::loadDocument($this->getRealPath(), true);
    }
}