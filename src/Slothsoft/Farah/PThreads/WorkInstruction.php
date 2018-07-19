<?php
namespace Slothsoft\Farah\PThreads;

class WorkInstruction
{
    public $className;
    public $options;
    public function __construct(string $className, array $options = []) {
        $this->className = $className;
        $this->options = $options;
    }
}

