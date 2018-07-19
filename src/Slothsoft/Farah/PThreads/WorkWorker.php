<?php
namespace Slothsoft\Farah\PThreads;

use Worker;

class WorkWorker extends Worker {
    public $logger;
    public $instructions;
    
    public function __construct(WorkEntries $logger, WorkEntries $instructions) {
        $this->logger = $logger;
        $this->instructions = $instructions;
    }
    
    public function run()   {
        require_once('vendor/autoload.php');
    }
    
    public function start(?int $options = PTHREADS_INHERIT_ALL) {
        return parent::start(PTHREADS_INHERIT_NONE);
    }
}

