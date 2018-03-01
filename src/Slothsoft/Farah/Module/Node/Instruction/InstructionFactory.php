<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Node\Instruction;

use Slothsoft\Core\XML\LeanElement;
use Slothsoft\Farah\Module\Module;
use Slothsoft\Farah\Module\Node\ModuleNodeFactory;
use DomainException;

/**
 *
 * @author Daniel Schulz
 *        
 */
class InstructionFactory extends ModuleNodeFactory
{
    protected function normalizeElementAttributes(LeanElement $element, LeanElement $parent = null)
    {}
    protected function instantiateNode(LeanElement $element): InstructionInterface
    {
        switch ($element->getTag()) {
            case Module::TAG_IMPORT:
                return new ImportInstruction();
            case Module::TAG_USE_DOCUMENT:
                return new UseDocumentInstruction();
            case Module::TAG_USE_TEMPLATE:
                return new UseTemplateInstruction();
            case Module::TAG_USE_STYLESHEET:
                return new UseStylesheetInstruction();
            case Module::TAG_USE_SCRIPT:
                return new UseScriptInstruction();
            case Module::TAG_PARAM:
                return new ParameterInstruction();
        }
        throw new DomainException("illegal tag: {$element->getTag()}");
    }
}

