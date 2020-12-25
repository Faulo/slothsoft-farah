<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\DOMWriter;

use Slothsoft\Core\IO\Writable\DOMWriterInterface;
use Slothsoft\Core\IO\Writable\Traits\DOMWriterDocumentFromElementTrait;
use DOMDocument;
use DOMElement;
use SplFileInfo;

class FileInfoDOMWriter implements DOMWriterInterface {
    use DOMWriterDocumentFromElementTrait;

    /**
     *
     * @var SplFileInfo
     */
    private $file;

    public function __construct(SplFileInfo $file) {
        $this->file = $file;
    }

    public function toElement(DOMDocument $targetDoc): DOMElement {
        $node = $targetDoc->createElement($this->file->getType());
        $node->setAttribute('name', $this->file->getFilename());
        if ($this->file->isFile()) {
            $node->setAttribute('size', $this->file->getSize());
            $node->setAttribute('modification-time', $this->file->getMTime());
            $node->setAttribute('modification-date', date(DATE_W3C, $this->file->getMTime()));
        }

        return $node;
    }
}