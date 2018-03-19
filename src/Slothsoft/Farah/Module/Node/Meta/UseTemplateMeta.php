<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Node\Meta;

use Slothsoft\Farah\Module\Node\AssetReferenceTrait;
use Slothsoft\Farah\Module\Node\Meta\InstructionInterfaces\UseTemplateInstruction;

/**
 *
 * @author Daniel Schulz
 *        
 */
class UseTemplateMeta extends MetaImplementation implements UseTemplateInstruction
{
    use AssetReferenceTrait {
        getReferencedAsset as public getReferencedTemplateAsset;
    }

    public function isUseTemplate(): bool
    {
        return true;
    }
}

