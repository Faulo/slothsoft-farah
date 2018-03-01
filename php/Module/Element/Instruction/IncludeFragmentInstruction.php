<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Element\Instruction;

use Slothsoft\Farah\Event\EventTargetInterface;

/**
 *
 * @author Daniel Schulz
 *        
 */
class IncludeFragmentInstruction extends GenericInstruction
{
    public function crawlAndFireAppropriateEvents(EventTargetInterface $listener) {;
        foreach ($this->getReferencedAsset()->getChildren() as $includedAsset) {
            $includedAsset->crawlAndFireAppropriateEvents($listener);
        }
    }
}

