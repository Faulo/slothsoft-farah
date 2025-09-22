<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Exception;

use Slothsoft\Farah\Module\Asset\AssetInterface;
use RuntimeException;

class AssetPathNotFoundException extends RuntimeException {
    
    public function __construct(AssetInterface $asset, string $missingPath, ?array $validPaths = null) {
        $message = "Asset '{$asset->getUrlPath()}' does not contain an asset '$missingPath'!";
        
        if ($validPaths !== null) {
            $message .= PHP_EOL;
            $message .= sprintf('  Existing assets are: [%s]', implode(', ', $validPaths));
        }
        
        parent::__construct($message);
    }
}

