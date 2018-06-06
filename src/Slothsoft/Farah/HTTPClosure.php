<?php
declare(strict_types = 1);
/**
 * *********************************************************************
 * Slothsoft\Farah\HTTPClosure v1.00 21.06.2017 © Daniel Schulz
 *
 * Changelog:
 * v1.00 21.06.2017
 * initial release
 * *********************************************************************
 */
namespace Slothsoft\Farah;

use Closure;

class HTTPClosure
{

    protected $options = [
        'isThreaded' => false,
        'isCachable' => false
    ];

    protected $task;

    public function __construct(array $options, Closure $task)
    {
        $this->options = $options + $this->options;
        $this->task = $task;
    }

    public function run(...$args)
    {
        return ($this->task)(...$args);
    }

    public function isThreaded()
    {
        return (bool) $this->options['isThreaded'];
    }

    public function isCachable()
    {
        return (bool) $this->options['isCachable'];
    }
}