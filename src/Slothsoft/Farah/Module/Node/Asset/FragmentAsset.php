<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Node\Asset;

use Slothsoft\Farah\Module\FarahUrl\FarahUrl;
use Slothsoft\Farah\Module\Node\Meta\InstructionInterfaces\UseDocumentInstruction;
use Slothsoft\Farah\Module\Node\Meta\InstructionInterfaces\UseTemplateInstruction;
use Slothsoft\Farah\Module\Results\ResultInterface;
use Throwable;
use Slothsoft\Farah\Module\Results\ResultCatalog;

/**
 *
 * @author Daniel Schulz
 *        
 */
class FragmentAsset extends ContainerAsset implements UseDocumentInstruction
{

    private $instructionsProcessed = false;

    private $documentInstructions = [];

    private $templateInstruction = null;

    private function processInstructions()
    {
        if (! $this->instructionsProcessed) {
            $this->instructionsProcessed = true;
            foreach ($this->getChildren() as $node) {
                if ($node instanceof UseTemplateInstruction) {
                    $this->templateInstruction = $node;
                }
                if ($node instanceof UseDocumentInstruction) {
                    $this->documentInstructions[] = $node;
                }
            }
        }
    }

    protected function loadResult(FarahUrl $url): ResultInterface
    {
        $this->processInstructions();
        $result = ResultCatalog::createTransformationResult($url, $this->getName());
        $result->setDocumentInstructions(...$this->documentInstructions);
        if ($this->templateInstruction) {
            $result->setTemplateInstruction($this->templateInstruction);
        }
        return $result;
    }

    protected function loadLinkedStylesheets(): array
    {
        $ret = parent::loadLinkedStylesheets();
        $this->processInstructions();
        foreach ($this->documentInstructions as $instruction) {
            try {
                $ret += $instruction->getReferencedDocumentAsset()->lookupLinkedStylesheets();
            } catch (Throwable $e) {}
        }
        return $ret;
    }

    protected function loadLinkedScripts(): array
    {
        $ret = parent::loadLinkedScripts();
        $this->processInstructions();
        foreach ($this->documentInstructions as $instruction) {
            try {
                $ret += $instruction->getReferencedDocumentAsset()->lookupLinkedScripts();
            } catch (Throwable $e) {}
        }
        return $ret;
    }

    public function getReferencedDocumentAsset(): AssetInterface
    {
        return $this;
    }

    public function getReferencedDocumentAlias(): string
    {
        return $this->getName();
    }
}

