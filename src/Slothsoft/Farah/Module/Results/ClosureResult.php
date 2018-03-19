<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Results;

use Slothsoft\Core\IO\HTTPFile;
use Slothsoft\Farah\Module\FarahUrl\FarahUrl;
use Closure;
use DOMDocument;
use DOMElement;

/**
 *
 * @author Daniel Schulz
 *        
 */
class ClosureResult extends ResultImplementation
{
    private $closure;
    private $result;
    
    public function __construct(FarahUrl $url, Closure $closure)
    {
        parent::__construct($url);
        
        $this->closure= $closure;
    }
    public function toElement(DOMDocument $targetDoc) : DOMElement
    {
        return $this->getResult()->toElement($targetDoc);
    }

    public function toFile() : HTTPFile
    {
        return $this->getResult()->toFile();
    }

    public function toString() : string
    {
        return $this->getResult()->toString();
    }

    public function toDocument() : DOMDocument
    {
        return $this->getResult()->toDocument();
    }
    
    public function exists() : bool
    {
        return $this->getResult()->exists();
    }
    
    private function getResult() : ResultInterface {
        if ($this->result === null) {
            $closure = $this->closure;
            $this->result = ResultCatalog::createFromMixed(
                $this->getUrl(),
                $closure($url)
            );
        }
        return $this->result;
    }
}

