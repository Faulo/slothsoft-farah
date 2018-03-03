<?php
namespace Slothsoft\Farah\Module\Results;

use Slothsoft\Farah\Module\AssetUses\DOMWriterInterface;
use Slothsoft\Farah\Module\AssetUses\FileWriterFromDOMTrait;
use Slothsoft\Farah\Module\FarahUrl\FarahUrl;
use DOMDocument;
use DOMElement;

/**
 *
 * @author Daniel Schulz
 *        
 */
class DOMWriterResult extends GenericResult
{
    use FileWriterFromDOMTrait;

    private $writer;

    public function __construct(FarahUrl $url, DOMWriterInterface $writer)
    {
        parent::__construct($url);
        
        $this->writer = $writer;
    }

    public function toElement(DOMDocument $targetDoc): DOMElement
    {
        return $this->writer->toElement($targetDoc);
    }

    public function toDocument(): DOMDocument
    {
        return $this->writer->toDocument();
    }

    public function exists(): bool
    {
        return true;
    }
}

