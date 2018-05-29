<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\DOMWriter;

use Slothsoft\Core\DOMHelper;
use Slothsoft\Core\IO\Writable\DOMWriterDocumentFromElementTrait;
use Slothsoft\Core\IO\Writable\DOMWriterInterface;
use Slothsoft\Farah\Module\Asset\AssetInterface;
use DOMDocument;
use DOMElement;
use Slothsoft\Farah\FarahUrl\FarahUrlArguments;

class AssetDocumentDOMWriter implements DOMWriterInterface
{
    use DOMWriterDocumentFromElementTrait;

    /**
     *
     * @var AssetInterface
     */
    private $asset;

    private $args;

    public function __construct(AssetInterface $asset, FarahUrlArguments $args)
    {
        $this->asset = $asset;
        $this->args = $args;
    }

    public function toElement(DOMDocument $targetDoc): DOMElement
    {
        $element = $this->asset->getManifestElement();
        $url = (string) $this->asset->createUrl();
        
        $node = $targetDoc->createElementNS(DOMHelper::NS_FARAH_MODULE, 'asset-document');
        $node->setAttribute('name', $element->getAttribute('name'));
        $node->setAttribute('url', $url);
        $node->setAttribute('href', str_replace('farah://', '/getAsset.php/', $url));
        $node->setAttribute('tag', $element->getTag());
        
        $node->appendChild($this->getDocumentContent()
            ->toElement($targetDoc));
        
        return $node;
    }

    private function getDocumentContent(): DOMWriterInterface
    {
        $valueAsset = $this->asset->getUseInstructionAsset();
        $executable = $valueAsset->lookupExecutable($this->args);
        return $executable->lookupXmlResult();
    }
}

