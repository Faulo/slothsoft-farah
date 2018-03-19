<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Node;

use Slothsoft\Core\XML\LeanElement;
use Slothsoft\Farah\Module\Module;
use Slothsoft\Farah\Module\FarahUrl\FarahUrlArguments;

/**
 *
 * @author Daniel Schulz
 *        
 */
interface ModuleNodeInterface
{

    public function initModuleNode(Module $ownerModule, LeanElement $element);

    public function getOwnerModule(): Module;

    public function getElement(): LeanElement;

    public function getElementTag(): string;

    public function getElementAttribute(string $key): string;

    public function hasElementAttribute(string $key): bool;

    public function getChildren(): array;

    public function createChildNode(LeanElement $element): ModuleNodeInterface;

    public function mergeWithManifestArguments(FarahUrlArguments $args): FarahUrlArguments;

    public function getManifestArguments(): FarahUrlArguments;

    public function __toString(): string;
}

