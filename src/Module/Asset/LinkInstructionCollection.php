<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Asset;

use Ds\Set;

class LinkInstructionCollection {
    
    public Set $stylesheetUrls;
    
    public Set $scriptUrls;
    
    public Set $moduleUrls;
    
    public function __construct() {
        $this->stylesheetUrls = new Set();
        $this->scriptUrls = new Set();
        $this->moduleUrls = new Set();
    }
    
    public function mergeWith(LinkInstructionCollection $other): void {
        $this->stylesheetUrls = $this->stylesheetUrls->union($other->stylesheetUrls);
        $this->scriptUrls = $this->scriptUrls->union($other->scriptUrls);
        $this->moduleUrls = $this->moduleUrls->union($other->moduleUrls);
    }
    
    public function isEmpty(): bool {
        return $this->stylesheetUrls->isEmpty() and $this->scriptUrls->isEmpty() and $this->moduleUrls->isEmpty();
    }
}

