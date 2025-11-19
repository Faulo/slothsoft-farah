<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\DOMWriter;

use Ds\Set;
use Slothsoft\Core\IO\Writable\DOMWriterInterface;
use Slothsoft\Core\IO\Writable\Traits\DOMWriterElementFromDocumentTrait;
use Slothsoft\Farah\Dictionary;
use DOMDocument;

final class TranslationDOMWriter2 implements DOMWriterInterface {
    use DOMWriterElementFromDocumentTrait;
    
    private DOMWriterInterface $source;
    
    private Dictionary $dict;
    
    private Set $dictionaries;
    
    public function __construct(DOMWriterInterface $source, Dictionary $dict, Set $dictionaryUrls) {
        $this->source = $source;
        $this->dict = $dict;
        $this->dictionaries = $dictionaryUrls;
    }
    
    public function toDocument(): DOMDocument {
        $document = $this->source->toDocument();
        
        if (! $this->dictionaries->isEmpty()) {
            my_dump($document->documentElement->tagName);
            $this->dict->translateDocumentViaDictionary($document, $this->dictionaries);
        }
        
        return $document;
    }
}

