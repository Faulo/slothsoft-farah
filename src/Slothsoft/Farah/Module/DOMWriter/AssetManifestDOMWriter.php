<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\DOMWriter;

use Slothsoft\Core\DOMHelper;
use Slothsoft\Core\IO\Writable\DOMWriterDocumentFromElementTrait;
use Slothsoft\Core\IO\Writable\DOMWriterInterface;
use Slothsoft\Farah\Module\Asset\AssetInterface;
use DOMDocument;
use DOMElement;

class AssetManifestDOMWriter implements DOMWriterInterface
{
    use DOMWriterDocumentFromElementTrait;

    /**
     *
     * @var AssetInterface
     */
    private $asset;

    public function __construct(AssetInterface $asset)
    {
        $this->asset = $asset;
    }

    public function toElement(DOMDocument $targetDoc): DOMElement
    {
        $element = $this->asset->getManifestElement();
        $url = (string) $this->asset->createUrl();
        
        $node = $targetDoc->createElementNS(DOMHelper::NS_FARAH_MODULE, 'asset-manifest');
        $node->setAttribute('name', $element->getAttribute('name'));
        $node->setAttribute('url', $url);
        $node->setAttribute('href', str_replace('farah://', '/getAsset.php/', $url));
        $node->setAttribute('tag', $element->getTag());
        
        return $node;
    }
}
