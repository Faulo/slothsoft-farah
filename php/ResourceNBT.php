<?php 
// © 2012 Daniel Schulz
namespace Slothsoft\Farah;

use Slothsoft\Minecraft\NBT\TAGNode;

class ResourceNBT extends Resource
{

    protected function loadFileXML()
    {
        $tagNode = TAGNode::createDocument($this->getContent());
        $domNode = TAGNode::TAG2DOM($this->resDoc, $tagNode);
        $domNode->setAttribute('key', $this->resNode->getAttribute('path'));
        $this->resNode->appendChild($domNode);
    }
}