<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Asset;

use Ds\Set;

class UseInstructionCollection {

    /**
     *
     * @var AssetInterface
     */
    public $rootUrl;

    /**
     *
     * @var AssetInterface[]
     */
    public $documentUrl;

    /**
     *
     * @var AssetInterface[]
     */
    public $manifestUrl;

    /**
     *
     * @var AssetInterface
     */
    public $templateUrl;

    public function __construct() {
        $this->rootUrl = null;
        $this->documentUrls = new Set();
        $this->manifestUrls = new Set();
        $this->templateUrl = null;
    }
}

