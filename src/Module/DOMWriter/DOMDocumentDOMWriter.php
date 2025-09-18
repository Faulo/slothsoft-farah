<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\DOMWriter;

use Slothsoft\Core\IO\Writable\DOMWriterInterface;
use Slothsoft\Core\IO\Writable\Traits\DOMWriterElementFromDocumentTrait;
use DOMDocument;

class DOMDocumentDOMWriter implements DOMWriterInterface {
    use DOMWriterElementFromDocumentTrait;
    
    private DOMDocument $document;
    
    public function __construct(DOMDocument $document) {
        $this->document = $document;
    }
    
    public function toDocument(): DOMDocument {
        return $this->document;
    }
}