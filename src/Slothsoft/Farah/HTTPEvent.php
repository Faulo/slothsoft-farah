<?php
declare(strict_types = 1);
namespace Slothsoft\Farah;

class HTTPEvent
{

    // implements https://dom.spec.whatwg.org/#interface-event
    public $type;

    public $target;

    public $data;

    public $timeStamp;

    public function __construct($type, array $eventInit = [])
    {
        $this->type = $type;
        $this->timeStamp = time();
    }
}