<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Executables;

use Slothsoft\Core\IO\Writable\DOMWriterInterface;
use Slothsoft\Farah\Module\FarahUrl\FarahUrlStreamIdentifier;
use Slothsoft\Farah\Module\Results\ResultCreator;
use Slothsoft\Farah\Module\Results\ResultInterface;

abstract class ExecutableDOMWriterBase extends ExecutableBase implements DOMWriterInterface {
    protected function loadResult(FarahUrlStreamIdentifier $type) : ResultInterface
    {
        $creator = new ResultCreator($this, $type);
        return $creator->createDOMWriterResult($this);
    }
}

