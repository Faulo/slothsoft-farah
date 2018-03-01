<?php

declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Results;

use Slothsoft\Core\DOMHelper;
use Slothsoft\Farah\HTTPFile;
use Slothsoft\Farah\Module\AssetUses\DOMWriterElementFromDocumentTrait;
use Slothsoft\Farah\Module\FarahUrl\FarahUrl;
use DOMDocument;

/**
 *
 * @author Daniel Schulz
 *        
 */
class DOMFileResult extends GenericResult
{
    use DOMWriterElementFromDocumentTrait;
    
    private $path;
    public function __construct(FarahUrl $url, string $path)
    {
        parent::__construct($url);
        
        $this->path = $path;
    }
    public function toDocument(): DOMDocument
    {
        return DOMHelper::loadDocument($this->path, false);
    }
    public function toFile(): HTTPFile
    {
        return HTTPFile::createFromPath($this->path);
    }
    public function toString(): string
    {
        return file_get_contents($this->path);
    }
    public function exists() : bool {
        return is_file($this->path);
    }
}

