<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Results\Files;

use Slothsoft\Core\DOMHelper;
use Slothsoft\Core\IO\Writable\DOMWriterElementFromDocumentTrait;
use DOMDocument;

/**
 *
 * @author Daniel Schulz
 *        
 */
class XmlFileResult extends FileResult
{
    use DOMWriterElementFromDocumentTrait;

    public function toDocument(): DOMDocument
    {
        return DOMHelper::loadDocument($this->toFile()->getPath(), false);
    }
}

