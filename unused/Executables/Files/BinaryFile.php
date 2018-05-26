<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Executables\Files;

use Slothsoft\Core\FileSystem;
use Slothsoft\Core\IO\Writable\DOMWriterDocumentFromElementTrait;
use DOMDocument;
use DOMElement;

class BinaryFile extends FileBase
{
    use DOMWriterDocumentFromElementTrait;

    public function toElement(DOMDocument $targetDoc): DOMElement
    {
        return FileSystem::asNode($this->getPath(), $targetDoc);
    }
}

