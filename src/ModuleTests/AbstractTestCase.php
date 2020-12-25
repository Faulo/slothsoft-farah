<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\ModuleTests;

use PHPUnit\Framework\TestCase;
use Throwable;

class AbstractTestCase extends TestCase {

    protected function failException(Throwable $e) {
        $this->fail(sprintf('%s: %s', basename(get_class($e)), $e->getMessage()));
    }

    protected function getObjectProperty(object $target, string $name) {
        $getProperty = function (string $name) {
            return $this->$name;
        };
        $getProperty = $getProperty->bindTo($target, get_class($target));
        return $getProperty($name);
    }

    protected function getObjectMethod(object $target, string $name, ...$args) {
        $getProperty = function (string $name, $args) {
            return $this->$name(...$args);
        };
        $getProperty = $getProperty->bindTo($target, get_class($target));
        return $getProperty($name, $args);
    }
}

