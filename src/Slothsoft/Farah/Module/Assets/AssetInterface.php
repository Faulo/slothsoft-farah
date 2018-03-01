<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Assets;

use Slothsoft\Core\XML\LeanElement;
use Slothsoft\Farah\Module\Module;
use Slothsoft\Farah\Module\AssetUses\DOMWriterInterface;
use Slothsoft\Farah\Module\Element\ModuleElementInterface;
use Slothsoft\Farah\Module\FarahUrl\FarahUrl;
use Slothsoft\Farah\Module\FarahUrl\FarahUrlArguments;
use Slothsoft\Farah\Module\FarahUrl\FarahUrlPath;
use Slothsoft\Farah\Module\ParameterFilters\ParameterFilterInterface;
use Slothsoft\Farah\Module\PathResolvers\PathResolverInterface;
use Slothsoft\Farah\Module\Results\ResultInterface;

/**
 *
 * @author Daniel Schulz
 *        
 */
interface AssetInterface extends DOMWriterInterface, ModuleElementInterface
{
    public function __toString() : string;
    
    public function initAsset(Module $ownerModule, LeanElement $element, array $children, FarahUrlPath $path);
    
    public function getUrlPath(): FarahUrlPath;

    public function getId(): string;

    public function getName(): string;

    public function getPath(): string;

    public function getRealPath(): string;

    public function getAssetPath(): string;

    
    
    public function getPathResolver(): PathResolverInterface;
    
    public function filterArguments(FarahUrlArguments $args): bool;
    
    public function getParameterFilter(): ParameterFilterInterface;
    public function traverseTo(string $path): AssetInterface;
    
    public function createUrl(FarahUrlArguments $args): FarahUrl;
    public function lookupResultByArguments(FarahUrlArguments $args): ResultInterface;
    
    
    
    
}

