<?php
namespace Slothsoft\Farah\Module\Results;

use Slothsoft\Farah\HTTPFile;
use Slothsoft\Farah\Module\AssetUses\DOMWriterDocumentFromElementTrait;
use Slothsoft\Farah\Module\AssetUses\FileWriterStringFromFileTrait;
use Slothsoft\Farah\Module\FarahUrl\FarahUrl;
use DOMDocument;
use DOMElement;

/**
 *
 * @author Daniel Schulz
 *        
 */
class BinaryFileResult extends GenericResult
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

