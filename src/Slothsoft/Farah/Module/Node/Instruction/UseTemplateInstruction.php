<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Node\Instruction;

use Slothsoft\Farah\Event\EventTargetInterface;
use Slothsoft\Farah\Module\Module;

/**
 *
 * @author Daniel Schulz
 *        
 */
class UseTemplateInstruction extends InstructionImplementation
{
    
    public function crawlAndFireAppropriateEvents(EventTargetInterface $listener) {
        $event = $this->createUseAssetEvent(Module::EVENT_USE_TEMPLATE);
        $listener->dispatchEvent($event);
    }
}

