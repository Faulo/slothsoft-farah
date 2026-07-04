<?php
declare(strict_types = 1);

namespace Slothsoft\Farah\Module\DOMWriter;

use DOMDocument;
use Slothsoft\Core\IO\Writable\DOMWriterInterface;
use Slothsoft\Core\IO\Writable\Traits\DOMWriterElementFromDocumentTrait;
use Slothsoft\Farah\Dictionary;
use Slothsoft\Farah\FarahUrl\FarahUrl;

/**
 * DOM writer that translates a Farah document with a dictionary language.
 *
 * @author Daniel Schulz
 * @since 2018-05-27
 */
final class TranslationDOMWriter implements DOMWriterInterface {
    use DOMWriterElementFromDocumentTrait;
    
    private DOMWriterInterface $source;
    
    private Dictionary $dict;
    
    private FarahUrl $context;
    
    public function __construct(DOMWriterInterface $source, Dictionary $dict, FarahUrl $context) {
        $this->source = $source;
        $this->dict = $dict;
        $this->context = $context;
    }
    
    public function toDocument(): DOMDocument {
        $document = $this->source->toDocument();
        $this->dict->translateDoc($document, $this->context);
        
        return $document;
    }
}

