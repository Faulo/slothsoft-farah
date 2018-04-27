<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Node;

use Slothsoft\Core\XML\LeanElement;
use Slothsoft\Farah\Module\Module;

/**
 *
 * @author Daniel Schulz
 *        
 */
interface ModuleNodeInterface
{
    public function __toString(): string;

    public function init(Module $ownerModule, LeanElement $element);
    
    public function getElementTag() : string;

    public function getChildren(): array;

    public function createChildNode(LeanElement $element): ModuleNodeInterface;
    
    /**
     * if true, $this must implement ImportInstructionInterface
     * 
     * @return bool
     */
    public function isImport(): bool;
    
    /**
     * if true, $this must implement ParameterInstructionInterface
     *
     * @return bool
     */
    public function isParameter(): bool;
    
    /**
     * if true, $this must implement UseManifestInstructionInterface
     *
     * @return bool
     */
    public function isUseManifest(): bool;

    /**
     * if true, $this must implement UseDocumentInstructionInterface
     *
     * @return bool
     */
    public function isUseDocument(): bool;

    /**
     * if true, $this must implement UseTemplateInstructionInterface
     *
     * @return bool
     */
    public function isUseTemplate(): bool;
    
    /**
     * if true, $this must implement UseStylesheetInstructionInterface
     *
     * @return bool
     */
    public function isUseStylesheet(): bool;

    /**
     * if true, $this must implement UseScriptInstructionInterface
     *
     * @return bool
     */
    public function isUseScript(): bool;
}

