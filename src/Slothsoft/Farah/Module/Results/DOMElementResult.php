<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Results;

use Slothsoft\Core\StreamWrapper\StreamWrapperInterface;
use Slothsoft\Farah\Module\FarahUrl\FarahUrl;
use Slothsoft\Farah\StreamWrapper\DocumentStreamWrapper;
use DOMDocument;
use DOMElement;

/**
 *
 * @author Daniel Schulz
 *        
 */
class DOMElementResult extends ResultImplementation
{
    private $element;

    public function __construct(FarahUrl $url, DOMElement $element)
    {
        parent::__construct($url);
        
        $this->element = $element;
    }
    
    /**
     * @override
     * @see \Slothsoft\Farah\Module\Results\ResultImplementation::loadDefaultStreamWrapper()
     */
    protected function loadDefaultStreamWrapper() : StreamWrapperInterface
    {
        return new DocumentStreamWrapper($this->toDocument());
    }
    /**
     * {@inheritDoc}
     * @see \Slothsoft\Farah\Module\Results\ResultImplementation::loadXmlStreamWrapper()
     */
    protected function loadXmlStreamWrapper() : StreamWrapperInterface
    {
        return new DocumentStreamWrapper($this->toDocument());
    }
    /**
     * {@inheritDoc}
     * @see \Slothsoft\Farah\Module\Results\ResultInterface::exists()
     */
    public function exists(): bool
    {
        return true;
    }
    
    
        
    /**
     * {@inheritDoc}
     * @see \Slothsoft\Farah\Module\Results\ResultImplementation::toDocument()
     */
    public function toDocument(): DOMDocument
    {
        $targetDoc = new DOMDocument();
        $targetDoc->appendChild($this->toElement($targetDoc));
        return $targetDoc;
    }
    /**
     * {@inheritDoc}
     * @see \Slothsoft\Farah\Module\Results\ResultImplementation::toElement()
     */
    public function toElement(DOMDocument $targetDoc): DOMElement
    {
        return $targetDoc->importNode($this->element, true);
    }
}

