<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Node\Instruction;

use Slothsoft\Farah\Event\EventTargetInterface;
use Slothsoft\Farah\Event\Events\SetParameterEvent;
use Slothsoft\Farah\Module\Module;

/**
 *
 * @author Daniel Schulz
 *        
 */
class ParameterInstruction extends InstructionImplementation
{

    public function getName(): string
    {
        return $this->getElementAttribute(Module::ATTR_PARAM_KEY);
    }

    public function getValue(): string
    {
        return $this->getElementAttribute(Module::ATTR_PARAM_VAL, '');
    }

    public function crawlAndFireAppropriateEvents(EventTargetInterface $listener)
    {
        $event = new SetParameterEvent();
        $event->initEvent(Module::EVENT_SET_PARAMETER, [
            'name' => $this->getName(),
            'value' => $this->getValue()
        ]);
        $listener->dispatchEvent($event);
    }
}

