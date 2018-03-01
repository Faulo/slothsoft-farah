<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Element\Instruction;

use Slothsoft\Core\XML\LeanElement;
use Slothsoft\Farah\Module\Module;
use Slothsoft\Farah\Module\Element\ModuleElementCreator;
use Slothsoft\Farah\Module\Element\ModuleElementFactoryInterface;
use DomainException;

/**
 *
 * @author Daniel Schulz
 *        
 */
class InstructionFactory implements ModuleElementFactoryInterface
{
    public function create(ModuleElementCreator $ownerCreator, Module $ownerModule, LeanElement $element, LeanElement $parent = null) : InstructionInterface
    {
        $instruction = $this->instantiateInstruction($element);
        $instruction->initInstruction(
            $ownerModule,
            $element,
            $ownerCreator->createList($ownerModule, $element->getChildren(), $element)
        );
        return $instruction;
    }
    private function instantiateInstruction(LeanElement $element): InstructionInterface
    {
        switch ($element->getTag()) {
            case Module::TAG_USE_DOCUMENT:
                return new UseDocumentInstruction();
            case Module::TAG_USE_TEMPLATE:
                return new UseTemplateInstruction();
            case Module::TAG_USE_STYLESHEET:
                return new UseStylesheetInstruction();
            case Module::TAG_USE_SCRIPT:
                return new UseScriptInstruction();
            case Module::TAG_INCLUDE_FRAGMENT:
                return new IncludeFragmentInstruction();
        }
        throw new DomainException("illegal tag: {$element->getTag()}");
    }
}

