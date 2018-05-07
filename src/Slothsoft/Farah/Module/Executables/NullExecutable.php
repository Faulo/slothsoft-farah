<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Executables;

use Slothsoft\Farah\Module\FarahUrl\FarahUrlStreamIdentifier;
use Slothsoft\Farah\Module\Results\ResultInterface;
use Slothsoft\Farah\Module\Results\ResultCreator;

class NullExecutable extends ExecutableBase
{

    protected function loadResult(FarahUrlStreamIdentifier $type): ResultInterface
    {
        $creator = new ResultCreator($this, $type);
        return $creator->createNullResult();
    }
}

