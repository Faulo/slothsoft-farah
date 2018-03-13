<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Results;

use Slothsoft\Core\IO\Writable\DOMWriterDocumentFromElementTrait;
use Slothsoft\Core\IO\Writable\FileWriterFromDOMTrait;
use Slothsoft\Farah\Module\FarahUrl\FarahUrl;
use DOMDocument;
use DOMElement;

/**
 *
 * @author Daniel Schulz
 *        
 */
class DOMElementResult extends ResultImplementation
{
    use FileWriterFromDOMTrait;
    use DOMWriterDocumentFromElementTrait;

    private $element;

    public function __construct(FarahUrl $url, DOMElement $element)
    {
        parent::__construct($url);
        
        $this->element = $element;
    }

    public function toElement(DOMDocument $targetDoc): DOMElement
    {
        return $targetDoc->importNode($this->element, true);
    }

    public function exists(): bool
    {
        return true;
    }
}

