<?php
/***********************************************************************
 * Slothsoft\Farah\ResourceText v1.00 19.10.2012 © Daniel Schulz
 * 
 * 	Changelog:
 *		v1.00 19.10.2012
 *			initial release
 ***********************************************************************/
namespace Slothsoft\Farah;

class ResourceText extends Resource
{

    protected function loadFileXML()
    {
        $this->resNode->appendChild($this->resDoc->createTextNode($this->getContent()));
    }
}