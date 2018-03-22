<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Node\Meta;

use Slothsoft\Farah\Module\Module;
use Slothsoft\Farah\Module\Node\Enhancements\ReferenceTrait;
use Slothsoft\Farah\Module\Node\Instruction\UseDocumentInstructionInterface;

/**
 *
 * @author Daniel Schulz
 *        
 */
class UseDocumentMeta extends MetaImplementation implements UseDocumentInstructionInterface
{
    use ReferenceTrait {
        getReferencedAsset as public getReferencedDocumentAsset;
    }

    public function getReferencedDocumentAlias(): string
    {
        return $this->getElementAttribute(Module::ATTR_ALIAS, $this->getReferencedDocumentAsset()
            ->getName());
    }

    public function isUseDocument(): bool
    {
        return true;
    }
}

