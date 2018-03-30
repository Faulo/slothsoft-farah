<?php
namespace Slothsoft\Farah\ModuleTests;

use PHPUnit\Framework\TestCase;
use Throwable;

class AbstractTestCase extends TestCase
{
    protected function failException(Throwable $e) {
        throw $e;
        $this->fail(sprintf('%s: %s', basename(get_class($e)), $e->getMessage()));
    }
}

