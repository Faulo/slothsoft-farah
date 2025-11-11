<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\LinkDecorator;

use Slothsoft\Farah\FarahUrl\FarahUrl;
use DOMDocument;

/**
 *
 * @author Daniel Schulz
 *        
 */
interface LinkDecoratorInterface {
    
    public function setTarget(DOMDocument $document): void;
    
    public function linkStylesheets(FarahUrl ...$stylesheets): void;
    
    public function linkScripts(FarahUrl ...$scripts): void;
    
    public function linkModules(FarahUrl ...$modules): void;
    
    public function linkContents(FarahUrl ...$modules): void;
}
