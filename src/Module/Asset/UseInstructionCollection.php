<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Asset;

use Ds\Set;
use Slothsoft\Core\IO\Writable\DOMWriterInterface;
use Slothsoft\Farah\FarahUrl\FarahUrl;
use Slothsoft\Farah\Module\DOMWriter\AssetDocumentDOMWriter;
use Slothsoft\Farah\Module\DOMWriter\AssetManifestDOMWriter;

class UseInstructionCollection {
    
    public FarahUrl $rootUrl;
    
    public Set $dataWriters;
    
    public ?FarahUrl $templateUrl = null;
    
    public function __construct(FarahUrl $rootUrl) {
        $this->rootUrl = $rootUrl;
        $this->dataWriters = new Set();
    }
    
    public function addData(DOMWriterInterface $dataWriter) {
        $this->dataWriters[] = $dataWriter;
    }
    
    public function addManifestData(FarahUrl $url, string $name) {
        $this->addData(new AssetManifestDOMWriter($url, $name));
    }
    
    public function addDocumentData(FarahUrl $url, string $name) {
        $this->addData(new AssetDocumentDOMWriter($url, $name));
    }
}

