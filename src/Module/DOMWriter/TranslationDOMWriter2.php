<?php
declare(strict_types = 1);

namespace Slothsoft\Farah\Module\DOMWriter;

use DOMDocument;
use Ds\Set;
use Slothsoft\Core\IO\Writable\DOMWriterInterface;
use Slothsoft\Core\IO\Writable\Traits\DOMWriterElementFromDocumentTrait;
use Slothsoft\Farah\Dictionary;
use Slothsoft\Farah\FarahUrl\FarahUrl;

/**
 * DOM writer variant for dictionary translation of Farah documents.
 *
 * @author Daniel Schulz
 * @since 2018-05-27
 */
final class TranslationDOMWriter2 implements DOMWriterInterface {
    use DOMWriterElementFromDocumentTrait;
    
    private DOMWriterInterface $source;
    
    private Dictionary $dict;
    
    /** @var Set<FarahUrl> */
    private Set $dictionaries;
    
    /**
     * @param Set<FarahUrl> $dictionaryUrls
     */
    public function __construct(DOMWriterInterface $source, Dictionary $dict, Set $dictionaryUrls) {
        $this->source = $source;
        $this->dict = $dict;
        $this->dictionaries = $dictionaryUrls;
    }
    
    public function toDocument(): DOMDocument {
        $document = $this->source->toDocument();
        
        if (! $this->dictionaries->isEmpty()) {
            $this->dict->translateDocumentViaDictionary($document, $this->dictionaries);
        }
        
        return $document;
    }
}

