<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Asset;

use Ds\Set;

class LinkInstructionCollection
{

    /**
     *
     * @var AssetInterface[]
     */
    public $stylesheetAssets;

    /**
     *
     * @var AssetInterface[]
     */
    public $scriptAssets;

    public function __construct()
    {
        $this->stylesheetAssets = new Set();
        $this->scriptAssets = new Set();
    }

    public function mergeWith(LinkInstructionCollection $other): void
    {
        $this->stylesheetAssets = $this->stylesheetAssets->union($other->stylesheetAssets);
        $this->scriptAssets = $this->scriptAssets->union($other->scriptAssets);
    }
}
