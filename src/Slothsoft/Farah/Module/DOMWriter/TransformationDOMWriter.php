<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\DOMWriter;

use Slothsoft\Core\DOMHelper;
use Slothsoft\Core\IO\Writable\DOMWriterElementFromDocumentTrait;
use Slothsoft\Core\IO\Writable\DOMWriterInterface;
use Slothsoft\Farah\Exception\EmptyTransformationException;
use DOMDocument;

class TransformationDOMWriter implements DOMWriterInterface
{
    use DOMWriterElementFromDocumentTrait;
    
    /**
     * @var DOMWriterInterface
     */
    private $source;
    
    /**
     * @var DOMWriterInterface
     */
    private $template;
    
    public function __construct(DOMWriterInterface $source, DOMWriterInterface $template) {
        $this->source = $source;
        $this->template = $template;
    }
    
    public function toDocument(): DOMDocument
    {
        $dom = new DOMHelper();
        
        $resultDoc = $dom->transformToDocument($this->source, $this->template);
        
        if (! $resultDoc->documentElement) {
            throw new EmptyTransformationException();
        }
        
        return $resultDoc;
    }

}

