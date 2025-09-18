<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\DOMWriter;

use Slothsoft\Core\DOMHelper;
use Slothsoft\Core\IO\Writable\DOMWriterInterface;
use Slothsoft\Core\IO\Writable\Traits\DOMWriterDocumentFromElementTrait;
use Slothsoft\Farah\FarahUrl\FarahUrl;
use Slothsoft\Farah\Module\Module;
use DOMDocument;
use DOMElement;

class AssetDocumentDOMWriter implements DOMWriterInterface {
    use DOMWriterDocumentFromElementTrait;
    
    /**
     *
     * @var FarahUrl
     */
    private $url;
    
    public function __construct(FarahUrl $url) {
        $this->url = $url;
    }
    
    public function toElement(DOMDocument $targetDoc): DOMElement {
        $childNode = Module::resolveToDOMWriter($this->url->withFragment('xml'))->toElement($targetDoc);
        
        $ns = (string) $childNode->namespaceURI;
        $name = basename((string) $this->url->getAssetPath());
        $id = htmlentities((string) $this->url, ENT_XML1);
        $href = str_replace('farah://', '/', $id);
        
        $xml = sprintf('<sfm:document-info xmlns:sfm="%s" xmlns="%s" version="1.1" name="%s" url="%s" href="%s" />', DOMHelper::NS_FARAH_MODULE, $ns, $name, $id, $href);
        
        $fragment = $targetDoc->createDocumentFragment();
        $fragment->appendXML($xml);
        $fragment->lastChild->appendChild($childNode);
        
        return $fragment->removeChild($fragment->lastChild);
    }
}

