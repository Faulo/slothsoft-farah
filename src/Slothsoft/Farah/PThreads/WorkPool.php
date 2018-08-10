<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\PThreads;

use Pool;

class WorkPool extends Pool
{

    private $workCount = 0;

    public function submitWork(WorkInstruction $instruction)
    {
        $class = $instruction->className;
        $options = $instruction->options;
        $work = new $class($options);
        $this->submit($work);
        $this->workCount ++;
    }

    public function hasWork(): bool
    {
        $this->collect(function (AbstractWorkThread $work) {
            if ($work->isGarbage()) {
                $this->workCount --;
                return true;
            } else {
                return false;
            }
        });
        return $this->workCount > 0;
    }
}

