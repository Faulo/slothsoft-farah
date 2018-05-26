<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\DOMWriter;

use Slothsoft\Core\DOMHelper;
use Slothsoft\Core\IO\Writable\DOMWriterDocumentFromElementTrait;
use Slothsoft\Core\IO\Writable\DOMWriterInterface;
use Slothsoft\Farah\Module\Asset\AssetInterface;
use DOMDocument;
use DOMElement;

class FragmentDOMWriter implements DOMWriterInterface
{
    use DOMWriterDocumentFromElementTrait;

    /**
     *
     * @var AssetInterface
     */
    private $asset;

    /**
     *
     * @var DOMWriterInterface[]
     */
    private $children;

    public function __construct(AssetInterface $asset)
    {
        $this->asset = $asset;
        $this->children = [];
    }

    public function appendChild(DOMWriterInterface $child)
    {
        $this->children[] = $child;
    }

    public function toElement(DOMDocument $targetDoc): DOMElement
    {
        $element = $this->asset->getManifestElement();
        $url = (string) $this->asset->createUrl();
        
        $node = $targetDoc->createElementNS(DOMHelper::NS_FARAH_MODULE, 'fragment');
        $node->setAttribute('name', $element->getAttribute('name'));
        $node->setAttribute('url', $url);
        $node->setAttribute('href', str_replace('farah://', '/getAsset.php/', $url));
        $node->setAttribute('type', $element->getTag());
        
        foreach ($this->children as $child) {
            $node->appendChild($child->toElement($targetDoc));
        }
        
        return $node;
    }
}

