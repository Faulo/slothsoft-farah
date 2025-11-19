<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Asset;

use Ds\Set;

class LinkInstructionCollection {
    
    public Set $stylesheetUrls;
    
    public Set $scriptUrls;
    
    public Set $moduleUrls;
    
    public Set $contentUrls;
    
    public Set $dictionaryUrls;
    
    public function __construct() {
        $this->stylesheetUrls = new Set();
        $this->scriptUrls = new Set();
        $this->moduleUrls = new Set();
        $this->contentUrls = new Set();
        $this->dictionaryUrls = new Set();
    }
    
    public function mergeWith(LinkInstructionCollection $other, bool $clearOther = false): void {
        if ($other->isEmpty()) {
            return;
        }
        
        $this->stylesheetUrls->add(...$other->stylesheetUrls);
        $this->scriptUrls->add(...$other->scriptUrls);
        $this->moduleUrls->add(...$other->moduleUrls);
        $this->contentUrls->add(...$other->contentUrls);
        $this->dictionaryUrls->add(...$other->dictionaryUrls);
        
        if ($clearOther) {
            $other->clear();
        }
    }
    
    private function clear() {
        $this->stylesheetUrls->clear();
        $this->scriptUrls->clear();
        $this->moduleUrls->clear();
        $this->contentUrls->clear();
        $this->dictionaryUrls->clear();
    }
    
    private function isEmpty(): bool {
        return $this->stylesheetUrls->isEmpty() and $this->scriptUrls->isEmpty() and $this->moduleUrls->isEmpty() and $this->contentUrls->isEmpty() and $this->dictionaryUrls->isEmpty();
    }
}

