<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Node\Asset;

use Slothsoft\Core\IO\Writable\DOMWriterInterface;
use Slothsoft\Farah\Module\FarahUrl\FarahUrl;
use Slothsoft\Farah\Module\FarahUrl\FarahUrlArguments;
use Slothsoft\Farah\Module\FarahUrl\FarahUrlPath;
use Slothsoft\Farah\Module\Node\ModuleNodeInterface;
use Slothsoft\Farah\Module\ParameterFilters\ParameterFilterInterface;
use Slothsoft\Farah\Module\PathResolvers\PathResolverInterface;
use Slothsoft\Farah\Module\Results\ResultInterface;

/**
 *
 * @author Daniel Schulz
 *        
 */
interface AssetInterface extends ModuleNodeInterface, DOMWriterInterface
{

    public function __toString(): string;

    public function getUrlPath(): FarahUrlPath;

    public function getId(): string;

    public function getName(): string;

    public function getAssetPath(): string;

    public function getPathResolver(): PathResolverInterface;

    public function filterArguments(FarahUrlArguments $args): bool;

    public function getParameterFilter(): ParameterFilterInterface;

    public function traverseTo(string $path): AssetInterface;

    public function createUrl(FarahUrlArguments $args): FarahUrl;

    public function lookupResultByArguments(FarahUrlArguments $args): ResultInterface;
}
