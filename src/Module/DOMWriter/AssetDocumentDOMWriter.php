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
        $this->name = $name ?? $url->getAssetPath()->getName();
    }
    
    public function toElement(DOMDocument $targetDoc): DOMElement {
        $url = (string) $this->url;
        $childNode = Module::resolveToDOMWriter($this->url->withStreamIdentifier(Executable::resultIsXml()))->toElement($targetDoc);
        
        $node = $targetDoc->createElementNS(DOMHelper::NS_FARAH_MODULE, 'sfm:document-info');
        
        if ($childNode->namespaceURI === null) {
            $node->setAttribute('xmlns', '');
        }
        
        $node->setAttribute('version', '1.1');
        $node->setAttribute('name', $this->name);
        $node->setAttribute('url', $url);
        $node->setAttribute('href', substr($url, strlen('farah:/')));
        
        $node->appendChild($childNode);
        
        return $node;
    }
}

