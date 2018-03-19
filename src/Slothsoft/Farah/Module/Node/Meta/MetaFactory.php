<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Node\Meta;

use Slothsoft\Core\DOMHelper;
use Slothsoft\Core\XML\LeanElement;
use Slothsoft\Farah\Exception\TagNotSupportedException;
use Slothsoft\Farah\Module\Module;
use Slothsoft\Farah\Module\Node\ModuleNodeFactory;
use Slothsoft\Farah\Module\Node\ModuleNodeInterface;

/**
 *
 * @author Daniel Schulz
 *        
 */
class MetaFactory extends ModuleNodeFactory
{

    protected function normalizeElementAttributes(LeanElement $element, LeanElement $parent = null)
    {}

    protected function instantiateNode(LeanElement $element): ModuleNodeInterface
    {
        switch ($element->getTag()) {
            case Module::TAG_IMPORT:
                return new ImportMeta();
            case Module::TAG_USE_DOCUMENT:
                return new UseDocumentMeta();
            case Module::TAG_USE_TEMPLATE:
                return new UseTemplateMeta();
            case Module::TAG_LINK_STYLESHEET:
                return new LinkStylesheetMeta();
            case Module::TAG_LINK_SCRIPT:
                return new LinkScriptMeta();
            case Module::TAG_PARAM:
                return new ParameterMeta();
            case Module::TAG_SOURCE:
            case Module::TAG_OPTIONS:
                return new MetaImplementation();
        }
        throw new TagNotSupportedException(DOMHelper::NS_FARAH_MODULE, $element->getTag());
    }
}

