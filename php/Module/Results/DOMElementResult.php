<?php
namespace Slothsoft\Farah\Module\Results;

use Slothsoft\Farah\Module\AssetUses\DOMWriterDocumentFromElementTrait;
use Slothsoft\Farah\Module\AssetUses\FileWriterFromDOMTrait;
use Slothsoft\Farah\Module\FarahUrl\FarahUrl;
use DOMDocument;
use DOMElement;

/**
 *
 * @author Daniel Schulz
 *        
 */
class DOMElementResult extends GenericResult
{
    use FileWriterFromDOMTrait;
    use DOMWriterDocumentFromElementTrait;

    private $element;
    public function __construct(FarahUrl $url, DOMElement $element)
    {
        parent::__construct($url);
        
        $this->element = $element;
    }
    public function toElement(DOMDocument $targetDoc) : DOMElement {
        return $targetDoc->importNode($this->element, true);
    }
    public function exists() : bool {
        return true;
    }
}

