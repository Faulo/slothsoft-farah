<?php
/***********************************************************************
 * Slothsoft\Farah\ResourceJSON v1.00 19.10.2012 © Daniel Schulz
 * 
 * 	Changelog:
 *		v1.00 19.10.2012
 *			initial release
 ***********************************************************************/
namespace Slothsoft\Farah;

class ResourceJSON extends Resource
{

    protected function loadFileXML()
    {
        $root = $this->value2dom($this->resDoc, json_decode($this->getContent(), false));
        $this->resNode->appendChild($root);
    }
}