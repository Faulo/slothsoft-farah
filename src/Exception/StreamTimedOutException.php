<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Exception;

class StreamTimedOutException extends \UnexpectedValueException {

    public function __construct(string $className) {
        parent::__construct("Stream '$className' timed out!");
    }
}

