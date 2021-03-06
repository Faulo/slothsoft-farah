<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Exception;

class TagNotSupportedException extends \BadMethodCallException {

    public function __construct(string $namespace, string $tag) {
        parent::__construct("<$tag xmlns=\"$namespace\"> is not supported by this implementation.");
    }
}

