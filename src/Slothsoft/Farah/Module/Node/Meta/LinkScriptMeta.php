<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Node\Meta;

use Slothsoft\Farah\Module\Node\AssetReferenceTrait;
use Slothsoft\Farah\Module\Node\Meta\InstructionInterfaces\LinkScriptInstruction;

/**
 *
 * @author Daniel Schulz
 *        
 */
class LinkScriptMeta extends MetaImplementation implements LinkScriptInstruction
{
    use AssetReferenceTrait {
        getReferencedAsset as public getReferencedScriptAsset;
    }
}

