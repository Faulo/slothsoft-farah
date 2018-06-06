<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Executable\ResultBuilderStrategy\Files;

use Slothsoft\Core\IO\Writable\DOMWriterElementFromDocumentTrait;
use DOMDocument;

class XmlFileResultBuilder extends AbstractFileResultBuilder
{
    use DOMWriterElementFromDocumentTrait;

    public function toDocument(): DOMDocument
    {
        $doc = new DOMDocument();
        $doc->load($this->file->getPath());
        return $doc;
    }
}
