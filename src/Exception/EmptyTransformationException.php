<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Exception;

class EmptyTransformationException extends \RuntimeException {
    
    public function __construct(string $uri) {
        parent::__construct("Template transformation '$uri' resulted in an empty document!");
    }
}
