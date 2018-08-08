<?php
namespace Slothsoft\Farah\PThreads;

use Worker;
use Slothsoft\Core\ServerEnvironment;

class WorkWorker extends Worker
{

    public $logger;

    public $instructions;

    public function __construct(WorkEntries $logger, WorkEntries $instructions)
    {
        $this->logger = $logger;
        $this->instructions = $instructions;
    }

    public function run()
    {
        if (class_exists(ServerEnvironment::class)) {
            require_once ServerEnvironment::getRootDirectory() . '/vendor/autoload.php';
        } else {
            require_once 'vendor/autoload.php';
        }
    }

    public function start(?int $options = 1118481)
    { // PTHREADS_INHERIT_ALL
        return parent::start(0); // PTHREADS_INHERIT_NONE
    }
}

