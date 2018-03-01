<?php

declare(strict_types = 1);
namespace Slothsoft\Farah\Event\Events;

use Slothsoft\Farah\Event\EventTargetInterface;

/**
 * inspired by https://dom.spec.whatwg.org/#event
 *
 * @author Daniel Schulz
 *        
 */
interface EventInterface
{

    public function initEvent(string $type, array $options);

    public function getType(): string;

    public function getTarget(): EventTargetInterface;

    public function getCurrentTarget(): EventTargetInterface;

    const NONE = 0;

    const CAPTURING_PHASE = 1;

    const AT_TARGET = 2;

    const BUBBLING_PHASE = 3;

    public function getEventPhase(): int;

    public function stopPropagation();

    public function fireEvent(EventTargetInterface $currentTarget, array $callbackList);
}

