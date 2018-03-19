<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Node\Meta;

use Slothsoft\Farah\Module\Node\AssetReferenceTrait;
use Slothsoft\Farah\Module\Node\Meta\InstructionInterfaces\LinkStylesheetInstruction;

/**
 *
 * @author Daniel Schulz
 *        
 */
class LinkStylesheetMeta extends MetaImplementation implements LinkStylesheetInstruction
{
    use AssetReferenceTrait {
        getReferencedAsset as public getReferencedStylesheetAsset;
    }
}

