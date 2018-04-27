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
class ImportMeta extends MetaBase implements ImportInstructionInterface
{
    use ReferenceTrait;
    
    public function isImport() : bool {
        return true;
    }

    public function getImportNodes(): array
    {
        return $this->getReferencedAsset()->getChildren();
    }
}

