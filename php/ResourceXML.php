<?php 
// © 2012 Daniel Schulz
namespace Slothsoft\Farah;

class ResourceXML extends Resource
{

    protected function loadFileXML()
    {
        if ($doc = HTTPDocument::loadDocument($this->getPath())) {
            if ($doc->documentElement) {
                $this->resNode = $doc->importNode($this->resNode, true);
                $this->resNode->appendChild($doc->documentElement);
                $doc->appendChild($this->resNode);
                $this->resDoc = $doc;
                /*
                 * $this->resNode->appendChild(
                 * $this->resDoc->importNode($doc->documentElement, true)
                 * );
                 * //
                 */
            }
        }
        /*
         * $this->resDoc->load($this->getPath());
         * foreach ($this->resNode->attributes as $attr) {
         * $this->resDoc->documentElement->setAttribute($attr->name, $attr->value);
         * }
         * $this->resNode = $this->resDoc->documentElement;
         * //
         */
    }
}