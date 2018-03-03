<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Event\Events;

use Slothsoft\Farah\Event\EventTargetInterface;

/**
 *
 * @author Daniel Schulz
 *        
 */
class GenericEvent implements EventInterface
{

    private $type;

    private $target;

    private $currentTarget;

    private $eventPhase = EventInterface::NONE;

    private $propagationStopped = false;

    public function initEvent(string $type, array $options)
    {
        $this->type = $type;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getTarget(): EventTargetInterface
    {
        return $this->target;
    }

    public function getCurrentTarget(): EventTargetInterface
    {
        return $this->currentTarget;
    }

    public function getEventPhase(): int
    {
        return $this->eventPhase;
    }

    public function stopPropagation()
    {
        $this->propagationStopped = true;
    }

    public function fireEvent(EventTargetInterface $currentTarget, array $callbackList): bool
    {
        $this->currentTarget = $currentTarget;
        if ($this->target === null) {
            $this->eventPhase = EventInterface::AT_TARGET;
            $this->target = $this->currentTarget;
        } else {
            $this->eventPhase = EventInterface::BUBBLING_PHASE;
        }
        foreach ($callbackList as $callback) {
            $callback($this);
        }
        return $this->propagationStopped;
    }
}

