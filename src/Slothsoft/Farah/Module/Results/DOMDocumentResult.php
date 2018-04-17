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
class DOMDocumentResult extends ResultImplementation
{

    private $document;

    public function __construct(FarahUrl $url, DOMDocument $doc)
    {
        parent::__construct($url);
        
        $this->document = $doc;
    }

    /**
     *
     * {@inheritdoc}
     * @see \Slothsoft\Farah\Module\Results\ResultImplementation::loadDefaultStreamWrapper()
     */
    protected function loadDefaultStreamWrapper(): StreamWrapperInterface
    {
        return new DocumentStreamWrapper($this->document);
    }

    /**
     *
     * {@inheritdoc}
     * @see \Slothsoft\Farah\Module\Results\ResultImplementation::loadXmlStreamWrapper()
     */
    protected function loadXmlStreamWrapper(): StreamWrapperInterface
    {
        return new DocumentStreamWrapper($this->document);
    }

    /**
     *
     * {@inheritdoc}
     * @see \Slothsoft\Farah\Module\Results\ResultInterface::exists()
     */
    public function exists(): bool
    {
        return true;
    }

    /**
     *
     * {@inheritdoc}
     * @see \Slothsoft\Farah\Module\Results\ResultImplementation::toDocument()
     */
    public function toDocument(): DOMDocument
    {
        return $this->document;
    }

    /**
     *
     * {@inheritdoc}
     * @see \Slothsoft\Farah\Module\Results\ResultImplementation::toElement()
     */
    public function toElement(DOMDocument $targetDoc): DOMElement
    {
        return $targetDoc->importNode($this->document->documentElement, true);
    }
}

