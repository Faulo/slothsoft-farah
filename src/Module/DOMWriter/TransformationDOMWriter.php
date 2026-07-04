<?php
declare(strict_types = 1);

namespace Slothsoft\Farah\Module\DOMWriter;

use DOMDocument;
use Slothsoft\Core\DOMHelper;
use Slothsoft\Core\IO\Writable\DOMWriterInterface;
use Slothsoft\Core\IO\Writable\Traits\DOMWriterElementFromDocumentTrait;
use Slothsoft\Farah\Exception\EmptyTransformationException;
use Slothsoft\Farah\Module\Manifest\Manifest;

/**
 * DOM writer that applies an XSLT transformation to a source document.
 *
 * @author Daniel Schulz
 * @since 2018-05-27
 */
final class TransformationDOMWriter implements DOMWriterInterface {
    use DOMWriterElementFromDocumentTrait;
    
    private DOMWriterInterface $source;
    
    private DOMWriterInterface $template;
    
    public function __construct(DOMWriterInterface $source, DOMWriterInterface $template) {
        $this->source = $source;
        $this->template = $template;
    }
    
    public function toDocument(): DOMDocument {
        $dom = new DOMHelper();
        
        $resultDoc = $dom->transformToDocument($this->source, $this->template);
        
        if (! $resultDoc->documentElement) {
            throw new EmptyTransformationException($this->source->toDocument()->documentElement->getAttribute(Manifest::ATTR_ID), $this->template->toDocument()->documentURI);
        }
        
        return $resultDoc;
    }
}

