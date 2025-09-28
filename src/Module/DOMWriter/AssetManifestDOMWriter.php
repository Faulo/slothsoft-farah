<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\DOMWriter;

use Slothsoft\Core\DOMHelper;
use Slothsoft\Core\IO\Writable\DOMWriterInterface;
use Slothsoft\Core\IO\Writable\Traits\DOMWriterDocumentFromElementTrait;
use Slothsoft\Farah\FarahUrl\FarahUrl;
use DOMDocument;
use DOMElement;

class AssetManifestDOMWriter implements DOMWriterInterface {
    use DOMWriterDocumentFromElementTrait;
    
    private FarahUrl $url;
    
    public function __construct(FarahUrl $url) {
        $this->url = $url;
    }
    
    public function toElement(DOMDocument $targetDoc): DOMElement {
        $id = (string) $this->url;
        $name = basename((string) $this->url->getAssetPath());
        $href = str_replace('farah://', '/', $id);
        
        $node = $targetDoc->createElementNS(DOMHelper::NS_FARAH_MODULE, 'sfm:manifest-info');
        $node->setAttribute('version', '1.1');
        $node->setAttribute('name', $name);
        $node->setAttribute('url', $id);
        $node->setAttribute('href', $href);
        
        return $node;
    }
}

