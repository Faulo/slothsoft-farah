<?php
declare(strict_types = 1);

namespace Slothsoft\Farah\Exception;

use InvalidArgumentException;
final class MalformedUrlException extends InvalidArgumentException {
    
    public function __construct(string $url) {
        parent::__construct("The URL '$url' could not be parsed.");
    }
}
