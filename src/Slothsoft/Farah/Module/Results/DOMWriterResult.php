<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Results;

use Slothsoft\Core\IO\Writable\DOMWriterInterface;
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
class DOMWriterResult extends ResultImplementation
{
    private $writer;

    public function __construct(FarahUrl $url, DOMWriterInterface $writer)
    {
        parent::__construct($url);
        
        $this->writer = $writer;
    }
    
    /**
     * {@inheritDoc}
     * @see \Slothsoft\Farah\Module\Results\ResultImplementation::loadDefaultStreamWrapper()
     */
    protected function loadDefaultStreamWrapper() : StreamWrapperInterface
    {
        return new DocumentStreamWrapper($this->writer->toDocument());
    }
    /**
     * {@inheritDoc}
     * @see \Slothsoft\Farah\Module\Results\ResultImplementation::loadXmlStreamWrapper()
     */
    protected function loadXmlStreamWrapper() : StreamWrapperInterface
    {
        return new DocumentStreamWrapper($this->writer->toDocument());
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
     * @see \Slothsoft\Farah\Module\Results\ResultImplementation::toElement()
     */
    public function toElement(DOMDocument $targetDoc): DOMElement
    {
        return $this->writer->toElement($targetDoc);
    }
    /**
     * {@inheritDoc}
     * @see \Slothsoft\Farah\Module\Results\ResultImplementation::toDocument()
     */
    public function toDocument(): DOMDocument
    {
        return $this->writer->toDocument();
    }
}

