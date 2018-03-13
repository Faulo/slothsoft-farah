<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Results;

use Slothsoft\Core\IO\Writable\DOMWriterElementFromDocumentTrait;
use Slothsoft\Core\IO\Writable\FileWriterFromDOMTrait;
use Slothsoft\Farah\Module\FarahUrl\FarahUrl;
use DOMDocument;

/**
 *
 * @author Daniel Schulz
 *        
 */
class DOMDocumentResult extends ResultImplementation
{
    use FileWriterFromDOMTrait;
    use DOMWriterElementFromDocumentTrait;

    private $document;

    public function __construct(FarahUrl $url, DOMDocument $doc)
    {
        parent::__construct($url);
        
        $this->document = $doc;
    }

    public function toDocument(): DOMDocument
    {
        return $this->document;
    }

    public function exists(): bool
    {
        return true;
    }
}

