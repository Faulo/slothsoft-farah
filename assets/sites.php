<?php

declare(strict_types = 1);

use Slothsoft\Farah\Kernel;

return function () {
    return Kernel::getInstance()->getSitesDocument();
};