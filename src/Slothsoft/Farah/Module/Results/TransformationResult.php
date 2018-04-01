<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Results;

use Slothsoft\Core\DOMHelper;
use Slothsoft\Core\IO\HTTPFile;
use Slothsoft\Core\IO\Writable\DOMWriterElementFromDocumentTrait;
use Slothsoft\Farah\Dictionary;
use Slothsoft\Farah\Exception\EmptyTransformationException;
use Slothsoft\Farah\Exception\ExceptionContext;
use Slothsoft\Farah\LinkDecorator\DecoratorFactory;
use Slothsoft\Farah\Module\FarahUrl\FarahUrl;
use Slothsoft\Farah\Module\FarahUrl\FarahUrlResolver;
use Slothsoft\Farah\Module\Node\InstructionCollector;
use Slothsoft\Farah\Module\Node\Instruction\UseDocumentInstructionInterface;
use Slothsoft\Farah\Module\Node\Instruction\UseManifestInstructionInterface;
use DOMDocument;
use DOMElement;
use Throwable;

/**
 *
 * @author Daniel Schulz
 *        
 */
class TransformationResult extends ResultImplementation
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

    public function __construct(FarahUrl $url, string $name, InstructionCollector $collector)
    {
        parent::__construct($url);
        
        $this->name = $name;
        $this->collector = $collector;
    }

    public function toDocument(): DOMDocument
    {
        if ($this->resultDoc === null) {
            $this->resultDoc = new DOMDocument();
            $dataNode = $this->resultDoc->createElementNS(DOMHelper::NS_FARAH_MODULE, FarahUrlResolver::resolveToAsset($this->getUrl())->getElement()->getTag());
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
                $asset = $this->collector->templateInstruction->getReferencedTemplateAsset();
                // echo "transforming $this using $asset ..." . PHP_EOL;
                $dom = new DOMHelper();
                
                if ($this->getId() === 'farah://slothsoft@slothsoft/home') {
                    // my_dump($this->resultDoc->saveXML());
                }
                
                $this->resultDoc = $dom->transformToDocument($this->resultDoc, $asset->createResult($this->getArguments())
                    ->toFile());
                
                if ($this->getId() === 'farah://slothsoft@slothsoft/home') {
                    // my_dump($this->resultDoc->saveXML());
                }
                
                if (! $this->resultDoc->documentElement) {
                    throw ExceptionContext::append(new EmptyTransformationException($asset), [
                        'asset' => $asset
                    ]);
                }
                
                // translating
                Dictionary::getInstance()->translateDoc($this->resultDoc, $asset->getOwnerModule());
            }
        }
        
        return $this->resultDoc;
    }

    public function exists(): bool
    {
        return true;
    }
    
    private function createElementFromDocumentInstruction(UseDocumentInstructionInterface $instruction) {
        $asset = $instruction->getReferencedDocumentAsset();
        
        $element = $this->createElement(
            self::TAG_DOCUMENT,
            $instruction->getReferencedDocumentAlias(),
            $asset->getId()
        );        
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
    private function createElementFromManifestInstruction(UseManifestInstructionInterface $instruction) : DOMElement {
        $asset = $instruction->getReferencedManifestAsset();
        $element = $this->createElement(
            $asset->getElement()->getTag(),
            $asset->getName(),
            $asset->getId()
        );
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
    public function toFile() : HTTPFile
    {
        $document = $this->toDocument();
        if ($document->documentElement) {
            $stylesheetList = $this->getLinkedStylesheets();
            $scriptList = $this->getLinkedScripts();
            if ($stylesheetList or $scriptList) {
                $decorator = DecoratorFactory::createForDocument($document);
                $decorator->linkStylesheets(...$stylesheetList);
                $decorator->linkScripts(...$scriptList);
            }
        }
        return HTTPFile::createFromDocument($document);
    }

    public function toString() : string
    {
        return $this->toFile()->getContents();
    }

}

