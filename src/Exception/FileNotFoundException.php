<?php
declare(strict_types = 1);

namespace Slothsoft\Farah\Exception;

use RuntimeException;
use SplFileInfo;

final class FileNotFoundException extends RuntimeException {
    
    public function __construct(SplFileInfo $file) {
        parent::__construct("File '$file' does not exist.");
    }
}
