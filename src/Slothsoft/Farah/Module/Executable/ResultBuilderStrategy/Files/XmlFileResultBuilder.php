<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Executable\ResultBuilderStrategy\Files;

use Slothsoft\Core\DOMHelper;
use Slothsoft\Core\IO\Writable\Traits\DOMWriterElementFromDocumentTrait;
use DOMDocument;

class XmlFileResultBuilder extends AbstractFileResultBuilder
{
    use DOMWriterElementFromDocumentTrait;

    public function toDocument(): DOMDocument
    {
        $document = DOMHelper::loadDocument((string) $this->file);
        $document->documentURI = (string) $this->url;
        return $document;
    }
}
