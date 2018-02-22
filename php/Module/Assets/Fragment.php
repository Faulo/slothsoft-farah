<?php

declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Assets;

use Slothsoft\Core\DOMHelper;
use Slothsoft\Farah\Dictionary;
use Slothsoft\Farah\Event\EventTargetInterface;
use Slothsoft\Farah\Event\Events\EventInterface;
use Slothsoft\Farah\Event\Events\UseAssetEvent;
use Slothsoft\Farah\Exception\ExceptionContext;
use Slothsoft\Farah\Module\FragmentProcessor;
use Slothsoft\Farah\Module\Module;
use Slothsoft\Farah\Module\AssetDefinitions\AssetDefinitionInterface;
use Slothsoft\Farah\Module\AssetUses\DOMWriterElementFromDocumentTrait;
use Slothsoft\Farah\Module\AssetUses\DOMWriterInterface;
use Slothsoft\Farah\Module\AssetUses\FileWriterFromDOMTrait;
use Slothsoft\Farah\Module\AssetUses\FileWriterInterface;
use BadFunctionCallException;
use DOMDocument;
use DOMElement;
use Throwable;

/**
 *
 * @author Daniel Schulz
 *        
 */
class Fragment extends GenericAsset implements DOMWriterInterface, FileWriterInterface, EventTargetInterface
{
    use FileWriterFromDOMTrait;
    use DOMWriterElementFromDocumentTrait;

    private $resultDoc;

    private $templateAsset;

    public function toDocument(): DOMDocument
    {
        if ($this->resultDoc === null) {
            $this->resultDoc = new DOMDocument();
            $dataNode = $this->resultDoc->createElementNS(DOMHelper::NS_FARAH_MODULE, Module::TAG_FRAGMENT);
            $dataNode->setAttribute(Module::ATTR_NAME, $this->getName());
            $dataNode->setAttribute(Module::ATTR_ID, $this->getId());
            $this->resultDoc->appendChild($dataNode);
            
            $processor = $this->createProcessor();
            
            $processor->addEventListener(Module::EVENT_USE_DOCUMENT, function (EventInterface $event) use ($processor) {
                if ($event instanceof UseAssetEvent) {
                    $asset = $event->getAsset();
                    $definition = $event->getDefinition();
                    
                    try {
                        $element = $this->createDocumentElement($definition, $asset);
                    } catch (Throwable $exception) {
                        ExceptionContext::append($exception, [
                            'asset' => $asset,
                            'definition' => $definition
                        ]);
                        $element = $exception->exceptionContext->toElement($this->resultDoc);
                    } finally {
                        $this->resultDoc->documentElement->appendChild($element);
                    }
                }
            });
            $processor->addEventListener(Module::EVENT_USE_TEMPLATE, function (EventInterface $event) {
                if ($event instanceof UseAssetEvent) {
                    $this->templateAsset = $event->getAsset();
                }
            });
            
            try {
                $processor->process();
            } catch (Throwable $exception) {
                ExceptionContext::append($exception, [
                    'asset' => $this
                ]);
                $element = $exception->exceptionContext->toElement($this->resultDoc);
                $dataNode->appendChild($element);
            }
            
            if ($this->templateAsset) {
                $dom = new DOMHelper();
                
                $this->resultDoc = $dom->transformToDocument($this->resultDoc, $this->templateAsset->toFile());
                
                if (! $this->resultDoc->documentElement) {
                    throw ExceptionContext::append(new BadFunctionCallException("Fragment template {$this->templateAsset->getId()} resulted in an empty document!"), [
                        'asset' => $this,
                        'definition' => $this->templateAsset->getDefinition()
                    ]);
                }
                
                // translating
                Dictionary::getInstance()->translateDoc($this->resultDoc, $this->getOwnerModule());
            }
        }
        
        return $this->resultDoc;
    }

    private function createProcessor(): FragmentProcessor
    {
        $processor = new FragmentProcessor($this);
        $processor->addEventAncestor($this);
        return $processor;
    }

    private function createDocumentElement(AssetDefinitionInterface $definition, AssetInterface $asset): DOMElement
    {
        $element = $this->resultDoc->createElementNS(DOMHelper::NS_FARAH_MODULE, Module::TAG_DOCUMENT);
        $element->setAttribute(Module::ATTR_NAME, $definition->getElementAttribute(Module::ATTR_ALIAS, $asset->getName()));
        $element->setAttribute(Module::ATTR_ID, $asset->getId());
        $element->setAttribute(Module::ATTR_HREF, str_replace('farah://', '/getAsset.php/', $asset->getId()));
        
        $element->appendChild($asset->toElement($element->ownerDocument));
        
        return $element;
    }
}

