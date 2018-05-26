<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Asset;

use Ds\Set;


class UseInstructionCollection
{
    /**
     * @var AssetInterface[]
     */
    public $documentAssets;
    
    /**
     * @var AssetInterface[]
     */
    public $manifestAssets;
    
    /**
     * @var AssetInterface
     */
    public $templateAsset;
    
    public function __construct() {
        $this->documentAssets = new Set();
        $this->manifestAssets = new Set();
        $this->templateAsset = null;
    }
}

