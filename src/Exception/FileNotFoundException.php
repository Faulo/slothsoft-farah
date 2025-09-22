<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Exception;

use SplFileInfo;

class FileNotFoundException extends \RuntimeException {
    
    public function __construct(SplFileInfo $file) {
        parent::__construct("File '$file' does not exist.");
    }
}
