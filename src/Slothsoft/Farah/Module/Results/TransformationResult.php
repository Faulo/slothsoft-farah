<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Results;

use Slothsoft\Core\DOMHelper;
use Slothsoft\Core\IO\HTTPFile;
use Slothsoft\Core\StreamWrapper\StreamWrapperInterface;
use Slothsoft\Farah\Dictionary;
use Slothsoft\Farah\Exception\EmptyTransformationException;
use Slothsoft\Farah\Exception\ExceptionContext;
use Slothsoft\Farah\LinkDecorator\DecoratorFactory;
use Slothsoft\Farah\Module\FarahUrl\FarahUrl;
use Slothsoft\Farah\Module\FarahUrl\FarahUrlResolver;
use Slothsoft\Farah\Module\Node\InstructionCollector;
use Slothsoft\Farah\Module\Node\Instruction\UseDocumentInstructionInterface;
use Slothsoft\Farah\Module\Node\Instruction\UseManifestInstructionInterface;
use Slothsoft\Farah\StreamWrapper\DocumentStreamWrapper;
use DOMDocument;
use DOMElement;
use Throwable;
use Slothsoft\Core\StreamWrapper\FileStreamWrapper;
use Slothsoft\Core\IO\Writable\DOMWriterElementFromDocumentTrait;
use Slothsoft\Core\IO\Writable\FileWriterStringFromFileTrait;

/**
 *
 * @author Daniel Schulz
 *        
 */
class TransformationResult extends ResultImplementation
{
    use DOMWriterElementFromDocumentTrait;
    use FileWriterStringFromFileTrait;
    
    const TAG_ROOT = 'fragment';

    const TAG_DOCUMENT = 'document';

    const ATTR_NAME = 'name';

    const ATTR_ID = 'url';

    const ATTR_HREF = 'href';

    private $name;

    private $collector;

    private $resultDoc;

    public function __construct(FarahUrl $url, string $name, InstructionCollector $collector)
    {
        parent::__construct($url);
        
        $this->name = $name;
        $this->collector = $collector;
    }
    
    
    
    /**
     * {@inheritDoc}
     * @see \Slothsoft\Farah\Module\Results\ResultImplementation::loadDefaultStreamWrapper()
     */
    protected function loadDefaultStreamWrapper() : StreamWrapperInterface {
        return new FileStreamWrapper($this->toFile());
    }
    /**
     * {@inheritDoc}
     * @see \Slothsoft\Farah\Module\Results\ResultImplementation::loadXmlStreamWrapper()
     */
    protected function loadXmlStreamWrapper() : StreamWrapperInterface {
        return new DocumentStreamWrapper($this->toDocument());
    }
    /**
     * {@inheritDoc}
     * @see \Slothsoft\Farah\Module\Results\ResultInterface::exists()
     */
    public function exists(): bool
    {
        return true;
    }

    
    
    /**
     * {@inheritDoc}
     * @see \Slothsoft\Farah\Module\Results\ResultImplementation::toDocument()
     */
    public function toDocument(): DOMDocument
    {
        if ($this->resultDoc === null) {
            $this->resultDoc = new DOMDocument();
            $dataNode = $this->resultDoc->createElementNS(DOMHelper::NS_FARAH_MODULE, FarahUrlResolver::resolveToAsset($this->getUrl())->getElement()
                ->getTag());
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
                $templateUrl = $templateAsset->createResult($this->getArguments())->createXmlUrl();
                
                $dom = new DOMHelper();
                
                $this->resultDoc = $dom->transformToDocument(
                    $this->resultDoc,
                    (string) $templateUrl
                );
                
                if (! $this->resultDoc->documentElement) {
                    throw ExceptionContext::append(new EmptyTransformationException($templateAsset), [
                        'asset' => $templateAsset
                    ]);
                }
                
                // translating
                Dictionary::getInstance()->translateDoc($this->resultDoc, $templateAsset->getOwnerModule());
            }
        }
        
        return $this->resultDoc;
    }

    private function createElementFromDocumentInstruction(UseDocumentInstructionInterface $instruction)
    {
        $asset = $instruction->getReferencedDocumentAsset();
        
        $element = $this->createElement(self::TAG_DOCUMENT, $instruction->getReferencedDocumentAlias(), $asset->getId());
        try {
            $result = $asset->createResult($this->getArguments());
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
        $element = $this->createElement($asset->getElement()
            ->getTag(), $asset->getName(), $asset->getId());
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

    
    
    /**
     * {@inheritDoc}
     * @see \Slothsoft\Farah\Module\Results\ResultImplementation::toFile()
     */
    public function toFile(): HTTPFile
    {
        $document = $this->toDocument();
        $stylesheetList = $this->getLinkedStylesheets();
        $scriptList = $this->getLinkedScripts();
        if ($stylesheetList or $scriptList) {
            $decorator = DecoratorFactory::createForDocument($document);
            $decorator->linkStylesheets(...$stylesheetList);
            $decorator->linkScripts(...$scriptList);
        }
        
        $extension = $this->guessExtension((string) $document->documentElement->namespaceURI);
        
        return HTTPFile::createFromDocument($document, "$this->name.$extension");
    }
    
    private function guessExtension(string $namespaceURI) {
        switch ($namespaceURI) {
            case DOMHelper::NS_HTML:    return 'xhtml';
            case DOMHelper::NS_SVG:     return 'svg';
            default:                    return 'xml';
        }
    }
}

