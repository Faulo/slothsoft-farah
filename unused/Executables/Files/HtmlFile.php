<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Executables\Files;

use Slothsoft\Core\IO\Writable\DOMWriterElementFromDocumentTrait;
use DOMDocument;

class HtmlFile extends FileBase
{
    use DOMWriterElementFromDocumentTrait;

    private $resultDoc;

    public function toDocument(): DOMDocument
    {
        if ($this->resultDoc === null) {
            $this->resultDoc = new DOMDocument();
            $this->resultDoc->loadHTMLFile($this->getPath());
        }
        return $this->resultDoc;
    }
}

