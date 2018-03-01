<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Element\Instruction;

use Slothsoft\Farah\Event\EventTargetInterface;
use Slothsoft\Farah\Module\Module;

/**
 *
 * @author Daniel Schulz
 *        
 */
class UseStylesheetInstruction extends GenericInstruction
{
    public function crawlAndFireAppropriateEvents(EventTargetInterface $listener) {
        $event = $this->createUseAssetEvent(Module::EVENT_USE_STYLESHEET);
        $listener->dispatchEvent($event);
    }
}

