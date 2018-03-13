<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Results;

use Slothsoft\Core\IO\Writable\DOMWriterInterface;
use Slothsoft\Core\IO\Writable\FileWriterFromDOMTrait;
use Slothsoft\Farah\Module\FarahUrl\FarahUrl;
use DOMDocument;
use DOMElement;

/**
 *
 * @author Daniel Schulz
 *        
 */
class DOMWriterResult extends ResultImplementation
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

