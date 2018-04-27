<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Executables;

use Slothsoft\Core\DOMHelper;
use Slothsoft\Core\IO\Writable\DOMWriterElementFromDocumentTrait;
use Slothsoft\Core\IO\Writable\DOMWriterInterface;
use Slothsoft\Farah\Dictionary;
use Slothsoft\Farah\Exception\EmptyTransformationException;
use Slothsoft\Farah\Exception\ExceptionContext;
use Slothsoft\Farah\LinkDecorator\DecoratedDOMWriter;
use Slothsoft\Farah\Module\FarahUrl\FarahUrlResolver;
use Slothsoft\Farah\Module\FarahUrl\FarahUrlStreamIdentifier;
use Slothsoft\Farah\Module\Node\InstructionCollector;
use Slothsoft\Farah\Module\Node\Instruction\UseDocumentInstructionInterface;
use Slothsoft\Farah\Module\Node\Instruction\UseManifestInstructionInterface;
use Slothsoft\Farah\Module\Results\ResultCreator;
use Slothsoft\Farah\Module\Results\ResultInterface;
use DOMDocument;
use DOMElement;
use Throwable;

/**
 *
 * @author Daniel Schulz
 *        
 */
class TransformationExecutable extends ExecutableBase implements DOMWriterInterface
{
    use DOMWriterElementFromDocumentTrait;
    
    const TAG_ROOT = 'fragment';

    const TAG_DOCUMENT = 'document';

    const ATTR_NAME = 'name';

    const ATTR_ID = 'url';

    const ATTR_HREF = 'href';

    private $name;

    private $collector;

    private $resultDoc;

    public function __construct(string $name, InstructionCollector $collector)
    {
        $this->name = $name;
        $this->collector = $collector;
    }
    
    protected function loadResult(FarahUrlStreamIdentifier $type): ResultInterface
    {
        $writer = $this;
        if ($type === self::resultIsDefault()) {
            //default result is the root transformation, so we gotta add all <link> and <script> elements
            $stylesheets = $this->getLinkedStylesheets();
            $scripts = $this->getLinkedScripts();
            if ($stylesheets or $scripts) {
                $writer = new DecoratedDOMWriter($writer, $stylesheets, $scripts);
            }
        }
        
        $resultCreator = new ResultCreator($this, $type);
        return $resultCreator->createDOMWriterResult($writer);
    }

    /**
     *
     * {@inheritdoc}
     * @see \Slothsoft\Farah\Module\Results\ResultBase::toDocument()
     */
    public function toDocument(): DOMDocument
    {
        if ($this->resultDoc === null) {
            $this->resultDoc = new DOMDocument();
            $dataNode = $this->resultDoc->createElementNS(DOMHelper::NS_FARAH_MODULE, $this->getOwnerAsset()->getElementTag());
            $dataNode->setAttribute(self::ATTR_NAME, $this->name);
            $dataNode->setAttribute(self::ATTR_ID, $this->getId());
            
            foreach ($this->collector->documentInstructions as $instruction) {
                $dataNode->appendChild($this->createElementFromDocumentInstruction($instruction));
            }
            
            foreach ($this->collector->manifestInstructions as $instruction) {
                $dataNode->appendChild($this->createElementFromManifestInstruction($instruction));
            }
            
            $this->resultDoc->appendChild($dataNode);
            
            if ($this->collector->templateInstruction) {
                $templateAsset = $this->collector->templateInstruction->getReferencedTemplateAsset();
                $templateUrl = $templateAsset->createUrl($this->getArguments(), self::resultIsXml());
                
                $dom = new DOMHelper();
                
                $this->resultDoc = $dom->transformToDocument($this->resultDoc, (string) $templateUrl);
                
                if (! $this->resultDoc->documentElement) {
                    throw ExceptionContext::append(new EmptyTransformationException($templateAsset), [
                        'asset' => $templateAsset
                    ]);
                }
                
                // translating
                Dictionary::getInstance()->translateDoc($this->resultDoc, FarahUrlResolver::resolveToModule($templateAsset->createUrl()));
            }
        }
        
        return $this->resultDoc;
    }

    private function createElementFromDocumentInstruction(UseDocumentInstructionInterface $instruction)
    {
        $asset = $instruction->getReferencedDocumentAsset();
        
        $element = $this->createElement(self::TAG_DOCUMENT, $instruction->getReferencedDocumentAlias(), $asset->getId());
        try {
            $result = $asset->lookupExecutable($this->getArguments())->lookupXmlResult();
            $element->appendChild($result->toElement($this->resultDoc));
        } catch (Throwable $exception) {
            ExceptionContext::append($exception, [
                'asset' => $asset,
                'class' => __CLASS__
            ]);
            $element = $exception->exceptionContext->toElement($this->resultDoc);
        }
        return $element;
    }

    private function createElementFromManifestInstruction(UseManifestInstructionInterface $instruction): DOMElement
    {
        $asset = $instruction->getReferencedManifestAsset();
        $element = $this->createElement($asset->getElementTag(), $asset->getName(), $asset->getId());
        return $element;
    }

    private function createElement(string $tag, string $name, string $id): DOMElement
    {
        $element = $this->resultDoc->createElementNS(DOMHelper::NS_FARAH_MODULE, $tag);
        $element->setAttribute(self::ATTR_NAME, $name);
        $element->setAttribute(self::ATTR_ID, $id);
        $element->setAttribute(self::ATTR_HREF, str_replace('farah://', '/getAsset.php/', $id));
        
        return $element;
    }
    
    private function getLinkedStylesheets(): array
    {
        return array_values($this->getOwnerAsset()->collectInstructions()->stylesheetAssets);
    }
    
    private function getLinkedScripts(): array
    {
        return array_values($this->getOwnerAsset()->collectInstructions()->scriptAssets);
    }
    
    private function guessFileName(DOMDocument $document) : string {
        return $this->name .'.'. $this->guessExtension((string) $document->documentElement->namespaceURI);
    }

    private function guessExtension(string $namespaceURI) : string
    {
        switch ($namespaceURI) {
            case DOMHelper::NS_HTML:
                return 'xhtml';
            case DOMHelper::NS_SVG:
                return 'svg';
            default:
                return 'xml';
        }
    }
}

