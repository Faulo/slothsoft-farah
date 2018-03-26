<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Node\Meta;

use Slothsoft\Farah\Module\Node\Enhancements\ReferenceTrait;
use Slothsoft\Farah\Module\Node\Instruction\UseScriptInstructionInterface;

/**
 *
 * @author Daniel Schulz
 *        
 */
class LinkScriptMeta extends MetaImplementation implements UseScriptInstructionInterface
{
    use ReferenceTrait {
        getReferencedAsset as public getReferencedScriptAsset;
    }

    public function isUseScript(): bool
    {
        return true;
    }
}

