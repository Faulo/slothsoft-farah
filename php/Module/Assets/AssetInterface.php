<?php

declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Assets;

use Slothsoft\Farah\Module\FarahUrl;
use Slothsoft\Farah\Module\Module;
use Slothsoft\Farah\Module\AssetDefinitions\AssetDefinitionInterface;
use DOMDocument;
use DOMElement;
use Slothsoft\Farah\Event\EventTargetInterface;

/**
 *
 * @author Daniel Schulz
 *        
 */
interface AssetInterface extends EventTargetInterface
{

    public function init(AssetDefinitionInterface $definition, FarahUrl $url);

    public function getDefinition(): AssetDefinitionInterface;

    public function getOwnerModule(): Module;

    public function lookupAsset(string $ref, array $args = []): AssetInterface;

    public function getName(): string;

    public function getPath(): string;

    public function getId(): string;

    public function getHref(): string;

    public function getUrl(): FarahUrl;

    public function getAssetPath(): string;

    public function getRealPath(): string;

    // public function setArguments(array $args);
    // public function withArguments(array $args) : AssetInterface;
    public function getArguments(): array;

    public function toDefinitionElement(DOMDocument $targetDoc): DOMElement;

    public function exists(): bool;
}

