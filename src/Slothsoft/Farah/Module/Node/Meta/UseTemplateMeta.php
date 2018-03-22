<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Node\Meta;

use Slothsoft\Farah\Module\Node\Enhancements\ReferenceTrait;
use Slothsoft\Farah\Module\Node\Instruction\UseTemplateInstructionInterface;

/**
 *
 * @author Daniel Schulz
 *        
 */
class UseTemplateMeta extends MetaImplementation implements UseTemplateInstructionInterface
{
    use ReferenceTrait {
        getReferencedAsset as public getReferencedTemplateAsset;
    }

    public function isUseTemplate(): bool
    {
        return true;
    }
}

