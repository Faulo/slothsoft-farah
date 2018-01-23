<?php
namespace Slothsoft\Farah\Module\Resources;

use Slothsoft\Core\DOMHelper;
use Slothsoft\Farah\HTTPFile;
use Slothsoft\Farah\Module\Module;
use Slothsoft\Farah\Module\Resource;
use Slothsoft\Farah\Module\AssetDefinitions\AssetDefinition;
use Slothsoft\Farah\Module\AssetDefinitions\ClosurableDefinition;
use Slothsoft\Farah\Module\AssetUses\DOMWriter;
use Slothsoft\Farah\Module\AssetUses\FileWriter;
use DOMDocument;
use DOMNode;

/**
 *
 * @author Daniel Schulz
 *        
 */
class PhpResource extends Resource
implements DOMWriter, FileWriter
{
    private $hasRun = false;
    private $result;
    
    public function init(AssetDefinition $definition)
    {
        parent::init($definition);
        
        assert($definition instanceof ClosurableDefinition, "PhpResource must be called with ClosurableDefinition");
    }
    
    public function toNode(DOMDocument $targetDoc = null) : DOMNode {
        $this->runClosure();
        
        $ret = null;
        switch (true) {
            case $this->result instanceof DOMDocument:
                $ret = $targetDoc
                    ? $targetDoc->importNode($this->result->documentElement, true)
                    : $this->result;
                break;
            case $this->result instanceof DOMNode:
                $ret = $targetDoc
                ? $targetDoc->importNode($this->result, true)
                : $this->result;
                break;
            default:
                $definition = $this->getDefinition();
                if ($targetDoc) {
                    $node = $targetDoc->createElementNS(DOMHelper::NS_FARAH_MODULE, $definition->getTag());
                    $ret = $node;
                } else {
                    $targetDoc = new DOMDocument();
                    $node = $targetDoc->createElementNS(DOMHelper::NS_FARAH_MODULE, $definition->getTag());
                    $targetDoc->appendChild($node);
                    $ret = $targetDoc;
                }
                $node->appendChild($targetDoc->createTextNode($this->result));
                break;
        }
        return $ret;
    }
    public function toFile() : HTTPFile
    {
        $this->runClosure();
        
        $ret = null;
        switch (true) {
            case $this->result instanceof DOMDocument:
                $ret = HTTPFile::createFromDocument($this->result);
                break;
            default:
                $ret = HTTPFile::createFromString($this->result, $this->getPath() . '.txt');
                break;
        }
        return $ret;
    }
    public function toString() : string {
        $this->runClosure();
        
        $ret = null;
        switch (true) {
            case $this->result instanceof DOMDocument:
                $ret = $this->result->saveXML();
                break;
            default:
                $ret = $this->result;
                break;
        }
        return $ret;
    }
    
    private function runClosure() {
        if ($this->hasRun === false) {
            $this->hasRun = true;
            $this->result = $this->getDefinition()->getClosure()->run();
        }
    }


}

