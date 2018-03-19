<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Results;

use Slothsoft\Core\DOMHelper;
use Slothsoft\Core\IO\Writable\DOMWriterElementFromDocumentTrait;
use Slothsoft\Core\IO\Writable\FileWriterFromDOMTrait;
use Slothsoft\Farah\Dictionary;
use Slothsoft\Farah\Exception\EmptyTransformationException;
use Slothsoft\Farah\Exception\ExceptionContext;
use Slothsoft\Farah\Module\FarahUrl\FarahUrl;
use Slothsoft\Farah\Module\FarahUrl\FarahUrlArguments;
use Slothsoft\Farah\Module\Node\Asset\AssetInterface;
use Slothsoft\Farah\Module\Node\Meta\InstructionInterfaces\UseDocumentInstruction;
use Slothsoft\Farah\Module\Node\Meta\InstructionInterfaces\UseTemplateInstruction;
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
    use FileWriterFromDOMTrait;
    use DOMWriterElementFromDocumentTrait;

    const TAG_ROOT = 'fragment';

    const TAG_DOCUMENT = 'document';

    const ATTR_NAME = 'name';

    const ATTR_ID = 'url';

    const ATTR_HREF = 'href';

    private $name;

    private $resultDoc;

    private $documentInstructions = [];

    private $templateInstruction = null;

    public function __construct(FarahUrl $url, string $name)
    {
        parent::__construct($url);
        
        $this->name = $name;
    }

    public function setDocumentInstructions(UseDocumentInstruction ...$instructions)
    {
        $this->documentInstructions = $instructions;
    }

    public function setTemplateInstruction(UseTemplateInstruction $instruction)
    {
        $this->templateInstruction = $instruction;
    }

    public function toDocument(): DOMDocument
    {
        if ($this->resultDoc === null) {
            $this->resultDoc = new DOMDocument();
            $dataNode = $this->resultDoc->createElementNS(DOMHelper::NS_FARAH_MODULE, self::TAG_ROOT);
            $dataNode->setAttribute(self::ATTR_NAME, $this->name);
            $dataNode->setAttribute(self::ATTR_ID, $this->getId());
            
            foreach ($this->documentInstructions as $instruction) {
                $asset = $instruction->getReferencedDocumentAsset();
                try {
                    $element = $this->createDocumentElement($asset, $this->getArguments(), $instruction->getReferencedDocumentAlias());
                } catch (Throwable $exception) {
                    ExceptionContext::append($exception, [
                        'asset' => $asset,
                        'class' => __CLASS__
                    ]);
                    $element = $exception->exceptionContext->toElement($this->resultDoc);
                } finally {
                    $dataNode->appendChild($element);
                }
            }
            
            $this->resultDoc->appendChild($dataNode);
            
            if ($this->templateInstruction) {
                $asset = $this->templateInstruction->getReferencedTemplateAsset();
                // echo "transforming $this using $asset ..." . PHP_EOL;
                $dom = new DOMHelper();
                
                if ($this->getId() === 'farah://slothsoft@slothsoft/home') {
                    // my_dump($this->resultDoc->saveXML());
                }
                
                $this->resultDoc = $dom->transformToDocument($this->resultDoc, $asset->lookupResultByArguments($this->getArguments())
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

    private function createDocumentElement(AssetInterface $asset, FarahUrlArguments $args, string $name): DOMElement
    {
        $element = $this->resultDoc->createElementNS(DOMHelper::NS_FARAH_MODULE, self::TAG_DOCUMENT);
        $element->setAttribute(self::ATTR_NAME, $name);
        $element->setAttribute(self::ATTR_ID, $asset->getId());
        $element->setAttribute(self::ATTR_HREF, str_replace('farah://', '/getAsset.php/', $asset->getId()));
        
        $element->appendChild($asset->lookupResultByArguments($args)
            ->toElement($element->ownerDocument));
        
        return $element;
    }
}

