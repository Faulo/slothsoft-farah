<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Asset;

use Ds\Set;
use Slothsoft\Farah\FarahUrl\FarahUrl;

class UseInstructionCollection {
    
    public FarahUrl $rootUrl;
    
    /**
     *
     * @var AssetInterface[]
     */
    public Set $documentUrls;
    
    /**
     *
     * @var AssetInterface[]
     */
    public Set $manifestUrls;
    
    public ?FarahUrl $templateUrl;
    
    public function __construct(FarahUrl $rootUrl) {
        $this->rootUrl = $rootUrl;
        $this->documentUrls = new Set();
        $this->manifestUrls = new Set();
    }
}

