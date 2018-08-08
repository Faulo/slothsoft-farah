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
interface LinkDecoratorInterface
{

    public function setNamespace(string $ns);

    public function setTarget(DOMDocument $document);

    public function linkStylesheets(FarahUrl ...$stylesheets);
    
    public function linkScripts(FarahUrl ...$scripts);
    
    public function linkModules(FarahUrl ...$scripts);
}

