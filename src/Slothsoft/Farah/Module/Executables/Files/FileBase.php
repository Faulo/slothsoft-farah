<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Executables\Files;

use Slothsoft\Farah\Module\Executables\ExecutableDOMWriterBase;
use Slothsoft\Farah\Module\FarahUrl\FarahUrlStreamIdentifier;
use Slothsoft\Farah\Module\Results\ResultCreator;
use Slothsoft\Farah\Module\Results\ResultInterface;

abstract class FileBase extends ExecutableDOMWriterBase
{
    private $path;
    public function __construct(string $path) {
        $this->path = $path;
    }
    
    protected function getPath() {
        return $this->path;
    }
    
    protected function loadResult(FarahUrlStreamIdentifier $type) : ResultInterface {
        $resultCreator = new ResultCreator($this, $type);
        if ($type === self::resultIsXml()) {
            return $resultCreator->createDOMWriterResult($this);
        } else {
            return $resultCreator->createFilePathResult($this->getPath());
        }
    }
}

