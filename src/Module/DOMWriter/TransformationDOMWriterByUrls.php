<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\DOMWriter;

use Slothsoft\Core\DOMHelper;
use Slothsoft\Core\IO\Writable\DOMWriterInterface;
use Slothsoft\Core\IO\Writable\Traits\DOMWriterElementFromDocumentTrait;
use Slothsoft\Farah\Exception\EmptyTransformationException;
use Slothsoft\Farah\FarahUrl\FarahUrl;
use Slothsoft\Farah\Module\Module;
use DOMDocument;

final class TransformationDOMWriterByUrls implements DOMWriterInterface {
    use DOMWriterElementFromDocumentTrait;
    
    private FarahUrl $source;
    
    private FarahUrl $template;
    
    public function __construct(FarahUrl $source, FarahUrl $template) {
        $this->source = $source;
        $this->template = $template;
    }
    
    public function toDocument(): DOMDocument {
        $dom = new DOMHelper();
        
        $resultDoc = $dom->transformToDocument(Module::resolveToDOMWriter($this->source), Module::resolveToDOMWriter($this->template));
        
        if (! $resultDoc->documentElement) {
            throw new EmptyTransformationException((string) $this->source, (string) $this->template);
        }
        
        return $resultDoc;
    }
}

