<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Asset;

use Ds\Set;

class LinkInstructionCollection
{

    /**
     *
     * @var Set
     */
    public $stylesheetUrls;

    /**
     *
     * @var Set
     */
    public $scriptUrls;

    public function __construct()
    {
        $this->stylesheetUrls = new Set();
        $this->scriptUrls = new Set();
    }

    public function mergeWith(LinkInstructionCollection $other): void
    {
        $this->stylesheetUrls = $this->stylesheetUrls->union($other->stylesheetUrls);
        $this->scriptUrls = $this->scriptUrls->union($other->scriptUrls);
    }
}

