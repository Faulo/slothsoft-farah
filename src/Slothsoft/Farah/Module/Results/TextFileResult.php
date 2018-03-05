<?php
namespace Slothsoft\Farah\Module\Results;

use Slothsoft\Core\IO\HTTPFile;
use Slothsoft\Core\IO\Writable\DOMWriterDocumentFromElementTrait;
use Slothsoft\Core\IO\Writable\FileWriterStringFromFileTrait;
use Slothsoft\Farah\Module\FarahUrl\FarahUrl;
use DOMDocument;
use DOMElement;

/**
 *
 * @author Daniel Schulz
 *        
 */
class TextFileResult extends ResultImplementation
{
    use DOMWriterDocumentFromElementTrait;
    use FileWriterStringFromFileTrait;

    private $path;

    public function __construct(FarahUrl $url, string $path)
    {
        parent::__construct($url);
        
        $this->path = $path;
    }

    public function toFile(): HTTPFile
    {
        return HTTPFile::createFromPath($this->path);
    }

    public function toElement(DOMDocument $targetDoc): DOMElement
    {
        return $targetDoc->createElement(basename(__CLASS__));
    }

    public function exists(): bool
    {
        return $this->toFile()->exists();
    }
}

