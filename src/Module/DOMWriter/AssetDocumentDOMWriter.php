<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\DOMWriter;

use Slothsoft\Core\DOMHelper;
use Slothsoft\Core\IO\Writable\DOMWriterInterface;
use Slothsoft\Core\IO\Writable\Traits\DOMWriterDocumentFromElementTrait;
use Slothsoft\Farah\FarahUrl\FarahUrl;
use Slothsoft\Farah\Module\Module;
use Slothsoft\Farah\Module\Executable\Executable;
use DOMDocument;
use DOMElement;

class AssetDocumentDOMWriter implements DOMWriterInterface {
    use DOMWriterDocumentFromElementTrait;
    
    private FarahUrl $url;
    
    private string $name;
    
    public function __construct(FarahUrl $url, ?string $name = null) {
        $this->url = $url;
        $this->name = $name ?? basename($url->getPath());
    }
    
    public function toElement(DOMDocument $targetDoc): DOMElement {
        $childNode = Module::resolveToDOMWriter($this->url->withStreamIdentifier(Executable::resultIsXml()))->toElement($targetDoc);
        
        $ns = (string) $childNode->namespaceURI;
        $id = htmlentities((string) $this->url, ENT_XML1);
        $href = str_replace('farah://', '/', $id);
        
        $xml = sprintf('<sfm:document-info xmlns:sfm="%s" xmlns="%s" version="1.1" name="%s" url="%s" href="%s" />', DOMHelper::NS_FARAH_MODULE, $ns, $this->name, $id, $href);
        
        $fragment = $targetDoc->createDocumentFragment();
        $fragment->appendXML($xml);
        $fragment->lastChild->appendChild($childNode);
        
        return $fragment->removeChild($fragment->lastChild);
    }
}

