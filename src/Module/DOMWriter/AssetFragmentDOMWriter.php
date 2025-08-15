<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\DOMWriter;

use Slothsoft\Core\DOMHelper;
use Slothsoft\Core\IO\Writable\DOMWriterInterface;
use Slothsoft\Core\IO\Writable\Traits\DOMWriterElementFromDocumentTrait;
use Slothsoft\Farah\FarahUrl\FarahUrl;
use DOMDocument;
use DOMImplementation;

class AssetFragmentDOMWriter implements DOMWriterInterface {
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

    public function __construct(FarahUrl $url) {
        $this->url = $url;
        $this->children = [];
    }

    public function appendChild(DOMWriterInterface $child) {
        $this->children[] = $child;
    }

    public function toDocument(): DOMDocument {
        $implementation = new DOMImplementation();

        $targetDoc = $implementation->createDocument(DOMHelper::NS_FARAH_MODULE, 'sfm:fragment-info');
        $node = $targetDoc->documentElement;

        $id = (string) $this->url;
        $targetDoc->documentURI = $id;
        $name = basename((string) $this->url->getAssetPath());
        $href = str_replace('farah://', '/', $id);

        $node->setAttribute('version', '1.1');
        $node->setAttribute('name', $name);
        $node->setAttribute('url', $id);
        $node->setAttribute('href', $href);

        $targetDoc->appendChild($node);

        foreach ($this->url->getArguments()->getValueList() as $key => $value) {
            $child = $targetDoc->createElementNS(DOMHelper::NS_FARAH_MODULE, 'sfm:param');
            $child->setAttribute('name', $key);
            if (is_string($value)) {
                $child->setAttribute('value', $value);
            }
            $node->appendChild($child);
        }

        foreach ($this->children as $child) {
            $node->appendChild($child->toElement($targetDoc));
        }

        return $targetDoc;
    }
}

