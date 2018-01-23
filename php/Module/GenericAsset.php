<?php
namespace Slothsoft\Farah\Module;

use Slothsoft\Farah\HTTPFile;
use Slothsoft\Farah\Module\AssetDefinitions\AssetDefinition;
use Slothsoft\Farah\Module\AssetUses\DOMWriter;
use Slothsoft\Farah\Module\AssetUses\FileWriter;
use DOMDocument;
use DOMNode;
use OutOfRangeException;

/**
 *
 * @author Daniel Schulz
 *
 */
class GenericAsset implements AssetInterface, DOMWriter, FileWriter
{
    private $definition;
    private $arguments = [];
    
    public function init(AssetDefinition $definition)
    {
        $this->definition = $definition;
        
        //echo 'initializing ' . $this->getId() . PHP_EOL;
    }
    public function getDefinition() : AssetDefinition {
        return $this->definition;
    }
    public function getName() : string
    {
        return $this->definition->getAttribute('name');
    }
    
    public function getPath() : string
    {
        return $this->definition->getAttribute('path');
    }
    
    public function getId() : string
    {
        return "farah://{$this->getOwnerModule()->getVendor()}@{$this->getOwnerModule()->getName()}{$this->getAssetPath()}";
    }
    
    public function getRealPath() : string
    {
        return $this->definition->getAttribute('realpath');
    }
    
    public function getAssetPath() : string
    {
        return $this->definition->getAttribute('assetpath');
    }
    
    public function getOwnerModule() : Module {
        return $this->definition->getOwnerModule();
    }
    public function lookupAsset(string $ref, array $args = []) : AssetInterface {
        $repository = Repository::getInstance();
        $module = $this->getOwnerModule();
        $uri = AssetUri::createFromReference($ref, $module, $args);
        return $repository->lookupAsset($uri);
    }
    
    public function withArguments(array $args) : AssetInterface
    {
        $ret = clone $this;
        $ret->setArguments($args);
        return $ret;
    }

    public function setArguments(array $args)
    {
        $this->arguments = $args;
    }

    public function getArguments() : array
    {
        return $this->arguments;
    }
    
    public function __toString() : string {
        return get_class($this) . ' ' . $this->getId();
    }
    
    public function toNode(DOMDocument $targetDoc = null) : DOMNode
    {
        if ($targetDoc) {
            return $this->getDefinition()->toNode($targetDoc);
        } else {
            $targetDoc = new DOMDocument();
            $targetDoc->appendChild($this->getDefinition()->toNode($targetDoc));
            return $targetDoc;
        }
    }
    public function toFile() : HTTPFile
    {
        return HTTPFile::createFromDocument($this->toNode());
    }
    public function toString() : string
    {
        return $this->toNode()->saveXML();
    }
}

