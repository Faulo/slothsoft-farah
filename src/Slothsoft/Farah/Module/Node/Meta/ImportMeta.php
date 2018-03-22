<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Node\Meta;

use Slothsoft\Farah\Module\Node\Enhancements\ReferenceTrait;
use Slothsoft\Farah\Module\Node\Instruction\ImportInstructionInterface;

/**
 *
 * @author Daniel Schulz
 *        
 */
class ImportMeta extends MetaImplementation implements ImportInstructionInterface
{
    use ReferenceTrait;

    public function getImportNodes(): array
    {
        return $this->getReferencedAsset()->getChildren();
    }
}

