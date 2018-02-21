<?php declare(strict_types=1);
namespace Slothsoft\Farah\Event;

use Slothsoft\Farah\Event\Events\EventInterface;

/**
 *
 * @author Daniel Schulz
 *        
 */
trait EventTargetTrait 
{

    private $eventAncestorList = [];

    private $eventListenerList = [];

    public function addEventAncestor(EventTargetInterface $target)
    {
        if (! in_array($target, $this->eventAncestorList, true)) {
            $this->eventAncestorList[] = $target;
        }
    }

    public function addEventListener(string $type, callable $callback)
    {
        if (! isset($this->eventListenerList[$type])) {
            $this->eventListenerList[$type] = [];
        }
        $this->eventListenerList[$type][] = $callback;
    }

    public function dispatchEvent(EventInterface $event)
    {
        $propagationStopped = $event->fireEvent($this, $this->eventListenerList[$event->getType()] ?? []);
        if ($propagationStopped === false) {
            foreach ($this->eventAncestorList as $ancestor) {
                $ancestor->dispatchEvent($event);
            }
        }
    }
}

