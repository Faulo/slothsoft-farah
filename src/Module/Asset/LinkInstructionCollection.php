<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Asset;

use Ds\Set;

class LinkInstructionCollection {
    
    public Set $stylesheetUrls;
    
    public Set $scriptUrls;
    
    public Set $moduleUrls;
    
    public Set $contentUrls;
    
    public function __construct() {
        $this->stylesheetUrls = new Set();
        $this->scriptUrls = new Set();
        $this->moduleUrls = new Set();
        $this->contentUrls = new Set();
    }
    
    public function mergeWith(LinkInstructionCollection $other, bool $clearOther = false): void {
        if ($other->isEmpty()) {
            return;
        }
        
        $this->stylesheetUrls = $this->stylesheetUrls->union($other->stylesheetUrls);
        $this->scriptUrls = $this->scriptUrls->union($other->scriptUrls);
        $this->moduleUrls = $this->moduleUrls->union($other->moduleUrls);
        $this->contentUrls = $this->contentUrls->union($other->contentUrls);
        
        if ($clearOther) {
            $other->clear();
        }
    }
    
    private function clear() {
        $this->stylesheetUrls->clear();
        $this->scriptUrls->clear();
        $this->moduleUrls->clear();
        $this->contentUrls->clear();
    }
    
    public function isEmpty(): bool {
        return $this->stylesheetUrls->isEmpty() and $this->scriptUrls->isEmpty() and $this->moduleUrls->isEmpty() and $this->contentUrls->isEmpty();
    }
}

