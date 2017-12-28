<?php
/***********************************************************************
 * Slothsoft\Farah\HTTPClosure v1.00 21.06.2017 © Daniel Schulz
 * 
 * 	Changelog:
 *		v1.00 21.06.2017
 *			initial release
 ***********************************************************************/
namespace Slothsoft\Farah;

declare(ticks = 1000);

use Closure;

class HTTPClosure
{

    protected $options = [
        'isThreaded' => false
    ];

    protected $task;

    public function __construct(array $options, Closure $task)
    {
        $this->options = $options + $this->options;
        $this->task = $task;
    }

    public function run()
    {
        return ($this->task)();
    }

    public function isThreaded()
    {
        return (bool) $this->options['isThreaded'];
    }
}