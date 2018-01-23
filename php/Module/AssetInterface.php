<?php
namespace Slothsoft\Farah\Module;

use DOMDocument;
use DOMElement;
use Slothsoft\Farah\HTTPFile;
use Slothsoft\Farah\Module\AssetDefinitions\AssetDefinition;

/**
 *
 * @author Daniel Schulz
 *        
 */
interface AssetInterface 
{
    public function init(AssetDefinition $definition);
    
    public function getDefinition() : AssetDefinition;
    
    public function getOwnerModule() : Module;
    
    public function lookupAsset(string $ref) : AssetInterface;
    
    public function getName() : string;
    public function getPath() : string;
    public function getId() : string;
    public function getAssetPath() : string;
    public function getRealPath() : string;
    
    public function setArguments(array $args);
    public function withArguments(array $args) : AssetInterface;
    public function getArguments() : array;
}

