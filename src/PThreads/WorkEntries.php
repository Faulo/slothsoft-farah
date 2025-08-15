<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\PThreads;

use Threaded;
use Volatile;

class WorkEntries extends Threaded {

    private $entries;

    public function __construct() {
        $this->entries = new Volatile();

        if (! extension_loaded('pthreads')) {
            // Threaded::data is NULL on initialization, should be array
            $property = new \ReflectionProperty(Threaded::class, 'data');
            $property->setAccessible(true);
            $property->setValue($this, []);
            $property->setValue($this->entries, []);
        }
    }

    public function append($entry): void {
        $this->entries->synchronized(function ($entry) {
            $this->entries[] = $entry;
        }, $entry);
    }

    public function fetch(): iterable {
        $ret = [];
        $this->entries->synchronized(function () use (&$ret) {
            while ($this->entries->count()) {
                $ret[] = $this->entries->shift();
            }
        });
        yield from $ret;
    }

    public function hasEntries(): bool {
        $ret = false;
        $this->entries->synchronized(function () use (&$ret) {
            $ret = (bool) $this->entries->count();
        });
        return $ret;
    }
}

