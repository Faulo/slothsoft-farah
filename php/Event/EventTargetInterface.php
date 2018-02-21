<?php declare(strict_types=1);
namespace Slothsoft\Farah\Event;

use Slothsoft\Farah\Event\Events\EventInterface;

/**
 *
 * @author Daniel Schulz
 *        
 */
interface EventTargetInterface
{

    public function addEventAncestor(EventTargetInterface $target);

    public function addEventListener(string $type, callable $callback);

    public function dispatchEvent(EventInterface $event);
}

