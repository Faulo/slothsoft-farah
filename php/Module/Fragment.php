<?php
namespace Slothsoft\Farah\Module;

use Slothsoft\Core\DOMHelper;
use Slothsoft\Farah\HTTPFile;
use Slothsoft\Farah\Kernel;
use Slothsoft\Farah\Module\AssetDefinitions\AssetDefinition;
use Slothsoft\Farah\Module\AssetUses\DOMWriter;
use Slothsoft\Farah\Module\AssetUses\FileWriter;
use DOMDocument;
use DOMDocumentFragment;
use DOMElement;
use DOMNode;
use RuntimeException;

/**
 *
 * @author Daniel Schulz
 *        
 */
class Fragment extends GenericAsset implements DOMWriter, FileWriter
{
    private $isLoading = false;
    
    private $dataAssetList;
    private $templateAssetList;
    private $scriptAssetList;
    private $styleAssetList;
    
    private $resultDoc;
    
    public function toNode(DOMDocument $targetDoc = null) : DOMNode
    {
        $resultDoc = $this->loadDocument();
        return $targetDoc
            ? $targetDoc->importNode($resultDoc->documentElement, true)
            : $resultDoc;
    }
    public function toFile() : HTTPFile
    {
        return HTTPFile::createFromDocument($this->toNode(), $this->getName() . '.xml');
    }
    public function toString() : string
    {
        return $this->toNode()->saveXML();
    }
    
    private function loadDocument() : DOMDocument
    {
        if (!$this->resultDoc) {
            if ($this->isLoading) {
                throw new RuntimeException("cycling fragment use detected in {$this->getId()}, aborting execution...");
            }
            
            $this->isLoading = true;
            $this->dataAssetList = [];
            $this->templateAssetList = [];
            $this->styleAssetList = [];
            $this->scriptAssetList = [];
            
            $fragmentNode = $this->getDefinition();
            foreach ($fragmentNode->getChildren() as $childNode) {
                $this->loadChildDefinition($childNode, $this);
            }
            
            
            $dataDoc = new DOMDocument();
            $dataNode = $dataDoc->createElementNS(DOMHelper::NS_FARAH_MODULE, Module::TAG_FRAGMENT);
            $dataNode->setAttribute('name', $this->getName());
            $dataNode->setAttribute('uri', $this->getId());
            foreach ($this->dataAssetList as $data) {
                $dataDefinition = $data[0];
                $dataAsset = $data[1];
                $element = $dataDoc->createElementNS(DOMHelper::NS_FARAH_MODULE, Module::TAG_DATA);
                $element->setAttribute('name', $dataDefinition->hasAttribute('as') ? $dataDefinition->getAttribute('as') : $dataAsset->getName());
                $element->setAttribute('uri', $dataAsset->getId());
                $element->appendChild($dataAsset->toNode($dataDoc));
                $dataNode->appendChild($element);
            }
            $dataDoc->appendChild($dataNode);
            
            $resultDoc = $dataDoc;
            
            foreach ($this->templateAssetList as $templateAsset) {
                $dom = new DOMHelper();
                $templateDoc = $templateAsset->toNode();
                assert($templateDoc instanceof DOMDocument, get_class($templateAsset)."::toNode() must return DOMDocument");
                
                $nodeList = $templateDoc->getElementsByTagNameNS(DOMHelper::NS_XSL, 'import');
                foreach ($nodeList as $node) {
                    $href = $node->getAttribute('href');
                    if (strpos($href, '/getTemplate.php') === 0) {
                        $xslName = substr($href, strlen('/getTemplate.php'));
                        $xslNS = '';
                        $this->canonicalizePath($xslName, $xslNS);
                        $node->setAttribute('href', 'http://slothsoft.net' . $href);
                        //$node->setAttribute('href', '../../' . $xslNS . '/' . self::DIR_TEMPLATE . $xslName . '.xsl');
                        // my_dump($node->getAttribute('href'));
                    }
                }
                $resultDoc = $dom->transform($dataDoc, $templateDoc);
                if (! $resultDoc->documentElement) {
                    throw new RuntimeException("template {$templateAsset->getId()} resulted in empty document!" . PHP_EOL . $dataDoc->saveXML());
                }
            }
            
            foreach ($this->styleAssetList as $styleAsset) {
                Kernel::getInstance()->getResponse()->addStyleFile($styleAsset->getRealPath());
            }
            
            foreach ($this->scriptAssetList as $scriptAsset) {
                Kernel::getInstance()->getResponse()->addScriptFile($scriptAsset->getRealPath());
            }
            
            $this->isLoading = false;
            
            $this->dataAssetList = null;
            $this->templateAssetList = null;
            $this->styleAssetList = null;
            $this->scriptAssetList = null;
            
            $this->resultDoc = $resultDoc;
        }
        return $this->resultDoc;
    }
    private function loadChildDefinition(AssetDefinition $assetNode, AssetInterface $contextAsset) {
        switch ($assetNode->getTag()) {
            case Module::TAG_INCLUDE_FRAGMENT:
                $asset = $contextAsset->lookupAsset($assetNode->getAttribute('ref'));
                $element = $asset->getDefinition();
                foreach ($element->getChildren() as $childNode) {
                    $this->loadChildDefinition($childNode, $asset);
                }
                break;
            case Module::TAG_USE_DATA:
                $asset = $contextAsset->lookupAsset($assetNode->getAttribute('ref'), $this->getArguments());
                assert($asset instanceof DOMWriter, "asset {$asset->getId()} must be a DOMWriter");
                $this->dataAssetList[] = [$assetNode, $asset];
                break;
            case Module::TAG_FRAGMENT:
                $ref = $contextAsset->getAssetPath() . '/' . $assetNode->getAttribute('name');
                $asset = $contextAsset->lookupAsset($ref, $this->getArguments());
                assert($asset instanceof DOMWriter, "asset {$asset->getId()} must be a DOMWriter");
                $this->dataAssetList[] = [$assetNode, $asset];
                break;
            case Module::TAG_USE_TEMPLATE:
                $asset = $contextAsset->lookupAsset($assetNode->getAttribute('ref'), $this->getArguments());
                assert($asset instanceof DOMWriter, "asset {$asset->getId()} must be a DOMWriter");
                $this->templateAssetList[] = $asset;
                break;
            case Module::TAG_USE_STYLESHEET:
                $asset = $contextAsset->lookupAsset($assetNode->getAttribute('ref'), $this->getArguments());
                assert($asset instanceof FileWriter, "asset {$asset->getId()} must be a FileWriter");
                $this->styleAssetList[] = $asset;
                break;
            case Module::TAG_USE_SCRIPT:
                $asset = $contextAsset->lookupAsset($assetNode->getAttribute('ref'), $this->getArguments());
                assert($asset instanceof FileWriter, "asset {$asset->getId()} must be a FileWriter");
                $this->scriptAssetList[] = $asset;
                break;
            case Module::TAG_PARAM:
                //TODO: <param>
                break;
            default:
                //echo $assetNode->tagName . PHP_EOL;
                break;
        }
    }
}

