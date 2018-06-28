<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\DOMWriter;

use Slothsoft\Core\DOMHelper;
use Slothsoft\Core\IO\Writable\DOMWriterElementFromDocumentTrait;
use Slothsoft\Core\IO\Writable\DOMWriterInterface;
use Slothsoft\Farah\FarahUrl\FarahUrl;
use DOMDocument;
use DOMImplementation;

class AssetFragmentDOMWriter implements DOMWriterInterface
{
use DOMWriterElementFromDocumentTrait;

    /**
     *
     * @var FarahUrl
     */
    private $url;

    /**
     *
     * @var DOMWriterInterface[]
     */
    private $children;

    public function __construct(FarahUrl $url)
    {
        $this->url = $url;
        $this->children = [];
    }

    public function appendChild(DOMWriterInterface $child)
    {
        $this->children[] = $child;
    }
    
    public function toDocument(): DOMDocument
    {
        $implementation = new DOMImplementation();
        
        $targetDoc = $implementation->createDocument(DOMHelper::NS_FARAH_MODULE, 'sfm:fragment');
        $node = $targetDoc->documentElement;
        
        $id = (string) $this->url;
        $name = basename((string) $this->url->getAssetPath());
        $href = str_replace('farah://', '/getAsset.php/', $id);
        
        $node->setAttribute('name', $name);
        $node->setAttribute('url', $id);
        $node->setAttribute('href', $href);
        
        $targetDoc->appendChild($node);
        
        foreach ($this->children as $child) {
            $node->appendChild($child->toElement($targetDoc));
        }
        
        return $targetDoc;
    }
}

