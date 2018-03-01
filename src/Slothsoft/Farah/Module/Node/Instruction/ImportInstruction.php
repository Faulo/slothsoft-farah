<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Node\Instruction;

use Slothsoft\Farah\Event\EventTargetInterface;

/**
 *
 * @author Daniel Schulz
 *        
 */
class ImportInstruction extends InstructionImplementation
{
    public function crawlAndFireAppropriateEvents(EventTargetInterface $listener) {;
        foreach ($this->getReferencedAsset()->getChildren() as $includedAsset) {
            $includedAsset->crawlAndFireAppropriateEvents($listener);
        }
    }
}

