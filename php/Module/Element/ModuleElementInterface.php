<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Element;

use Slothsoft\Core\XML\LeanElement;
use Slothsoft\Farah\Event\EventTargetInterface;
use Slothsoft\Farah\Module\Module;
use Slothsoft\Farah\Module\FarahUrl\FarahUrlArguments;

/**
 *
 * @author Daniel Schulz
 *        
 */
interface ModuleElementInterface 
{

    public function initModuleElement(Module $ownerModule, LeanElement $element, array $children);

    public function getOwnerModule(): Module;

    public function getElement(): LeanElement;

    public function getElementTag(): string;

    public function getElementAttribute(string $key): string;

    public function hasElementAttribute(string $key): bool;
    
    public function getChildren(): array;
    
    public function addChildElement(LeanElement $element): ModuleElementInterface;
    
    public function mergeWithManifestArguments(FarahUrlArguments $args): FarahUrlArguments;
    
    public function getManifestArguments(): FarahUrlArguments;
    
    public function crawlAndFireAppropriateEvents(EventTargetInterface $listener);
    
    public function __toString() : string;
}

