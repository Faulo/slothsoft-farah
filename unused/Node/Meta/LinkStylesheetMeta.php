<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Node\Meta;

use Slothsoft\Farah\Module\Node\Enhancements\ReferenceTrait;
use Slothsoft\Farah\Module\Node\Instruction\UseStylesheetInstructionInterface;

/**
 *
 * @author Daniel Schulz
 *        
 */
class LinkStylesheetMeta extends MetaBase implements UseStylesheetInstructionInterface
{
    use ReferenceTrait {
        getReferencedAsset as public getReferencedStylesheetAsset;
    }

    public function isUseStylesheet(): bool
    {
        return true;
    }
}

