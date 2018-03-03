<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Results;

use Slothsoft\Core\DOMHelper;
use Slothsoft\Core\IO\Writable\DOMWriterElementFromDocumentTrait;
use Slothsoft\Core\IO\Writable\FileWriterFromDOMTrait;
use Slothsoft\Farah\Dictionary;
use Slothsoft\Farah\Event\EventTargetInterface;
use Slothsoft\Farah\Event\EventTargetTrait;
use Slothsoft\Farah\Event\Events\EventInterface;
use Slothsoft\Farah\Event\Events\UseAssetEvent;
use Slothsoft\Farah\Exception\ExceptionContext;
use Slothsoft\Farah\Module\FragmentProcessor;
use Slothsoft\Farah\Module\Module;
use Slothsoft\Farah\Module\FarahUrl\FarahUrl;
use Slothsoft\Farah\Module\FarahUrl\FarahUrlArguments;
use Slothsoft\Farah\Module\Node\Asset\AssetInterface;
use BadFunctionCallException;
use DOMDocument;
use DOMElement;
use Throwable;

/**
 *
 * @author Daniel Schulz
 *        
 */
class FragmentResult extends GenericResult implements EventTargetInterface
{
    use FileWriterFromDOMTrait;
    use DOMWriterElementFromDocumentTrait;
    use EventTargetTrait;

    private $asset;

    private $resultDoc;

    private $templateAsset;

    public function __construct(FarahUrl $url, AssetInterface $asset)
    {
        parent::__construct($url);
        
        $this->asset = $asset;
    }

    public function toDocument(): DOMDocument
    {
        if ($this->resultDoc === null) {
            $this->resultDoc = new DOMDocument();
            $dataNode = $this->resultDoc->createElementNS(DOMHelper::NS_FARAH_MODULE, Module::TAG_FRAGMENT);
            $dataNode->setAttribute(Module::ATTR_NAME, $this->asset->getName());
            $dataNode->setAttribute(Module::ATTR_ID, $this->getId());
            $this->resultDoc->appendChild($dataNode);
            
            $processor = $this->createProcessor();
            $processor->addEventListener(Module::EVENT_USE_DOCUMENT, [
                $this,
                'onUseDocument'
            ]);
            $processor->addEventListener(Module::EVENT_USE_TEMPLATE, [
                $this,
                'onUseTemplate'
            ]);
            
            try {
                $processor->process();
            } catch (Throwable $exception) {
                ExceptionContext::append($exception, [
                    'result' => $this,
                    'class' => __CLASS__
                ]);
                $element = $exception->exceptionContext->toElement($this->resultDoc);
                $dataNode->appendChild($element);
            }
            
            if ($this->templateAsset) {
                // echo "transforming $this using $this->templateAsset ..." . PHP_EOL;
                $dom = new DOMHelper();
                
                if ($this->getId() === 'farah://slothsoft@slothsoft/home') {
                    // my_dump($this->resultDoc->saveXML());
                }
                
                $this->resultDoc = $dom->transformToDocument($this->resultDoc, $this->templateAsset->lookupResultByArguments($this->getArguments())
                    ->toFile());
                
                if ($this->getId() === 'farah://slothsoft@slothsoft/home') {
                    // my_dump($this->resultDoc->saveXML());
                }
                
                if (! $this->resultDoc->documentElement) {
                    throw ExceptionContext::append(new BadFunctionCallException("Fragment template $this->templateAsset resulted in an empty document!"), [
                        'asset' => $this->templateAsset
                    ]);
                }
                
                // translating
                Dictionary::getInstance()->translateDoc($this->resultDoc, $this->asset->getOwnerModule());
            }
        }
        
        return $this->resultDoc;
    }

    public function exists(): bool
    {
        return true;
    }

    public function onUseDocument(EventInterface $event)
    {
        if ($event instanceof UseAssetEvent) {
            $asset = $event->getAsset();
            $args = $event->getAssetArguments();
            $alias = $event->getAssetName();
            
            try {
                $element = $this->createDocumentElement($asset, FarahUrlArguments::createFromMany($args, $this->getArguments()), $alias);
            } catch (Throwable $exception) {
                ExceptionContext::append($exception, [
                    'asset' => $asset,
                    'class' => __CLASS__
                ]);
                $element = $exception->exceptionContext->toElement($this->resultDoc);
            } finally {
                $this->resultDoc->documentElement->appendChild($element);
            }
        }
    }

    public function onUseTemplate(EventInterface $event)
    {
        if ($event instanceof UseAssetEvent) {
            $this->templateAsset = $event->getAsset();
        }
    }

    private function createProcessor(): FragmentProcessor
    {
        $processor = new FragmentProcessor($this->asset);
        $processor->addEventAncestor($this->asset->getOwnerModule());
        return $processor;
    }

    private function createDocumentElement(AssetInterface $asset, FarahUrlArguments $args, string $name): DOMElement
    {
        $element = $this->resultDoc->createElementNS(DOMHelper::NS_FARAH_MODULE, Module::TAG_DOCUMENT);
        $element->setAttribute(Module::ATTR_NAME, $name);
        $element->setAttribute(Module::ATTR_ID, $asset->getId());
        $element->setAttribute(Module::ATTR_HREF, str_replace('farah://', '/getAsset.php/', $asset->getId()));
        
        $element->appendChild($asset->lookupResultByArguments($args)
            ->toElement($element->ownerDocument));
        
        return $element;
    }
}

