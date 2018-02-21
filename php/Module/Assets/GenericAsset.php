<?php declare(strict_types=1);
namespace Slothsoft\Farah\Module\Assets;

use Slothsoft\Core\DOMHelper;
use Slothsoft\Farah\Event\EventTargetTrait;
use Slothsoft\Farah\Module\AssetRepository;
use Slothsoft\Farah\Module\FarahUrl;
use Slothsoft\Farah\Module\Module;
use Slothsoft\Farah\Module\AssetDefinitions\AssetDefinitionInterface;
use DOMDocument;
use DOMElement;

/**
 *
 * @author Daniel Schulz
 *        
 */
class GenericAsset implements AssetInterface
{
    use EventTargetTrait;

    private $definition;

    private $url;

    public function init(AssetDefinitionInterface $definition, FarahUrl $url)
    {
        $this->definition = $definition;
        $this->url = $url;
        
        // echo 'initializing ' . $this->getId() . PHP_EOL;
    }

    public function getUrl(): FarahUrl
    {
        return $this->url;
    }

    public function getDefinition(): AssetDefinitionInterface
    {
        return $this->definition;
    }

    public function getName(): string
    {
        return $this->definition->getAttribute('name');
    }

    public function getPath(): string
    {
        return $this->definition->getAttribute('path');
    }

    public function getId(): string
    {
        return $this->url->toString();
    }

    public function getHref(): string
    {
        return str_replace('farah://', '/getAsset.php/', $this->getId());
    }

    public function getRealPath(): string
    {
        return $this->definition->getAttribute('realpath');
    }

    public function getAssetPath(): string
    {
        return $this->definition->getAttribute('assetpath');
    }

    public function getOwnerModule(): Module
    {
        return $this->definition->getOwnerModule();
    }

    public function lookupAsset(string $ref, array $args = []): AssetInterface
    {
        $repository = AssetRepository::getInstance();
        $module = $this->getOwnerModule();
        $url = FarahUrl::createFromReference($ref, $module, $args);
        return $repository->lookupAssetByUrl($url);
    }

    public function getArguments(): array
    {
        return $this->url->getQueryArray();
    }

    public function toDefinitionElement(DOMDocument $targetDoc): DOMElement
    {
        $node = $targetDoc->createElementNS(DOMHelper::NS_FARAH_MODULE, $this->definition->getTag());
        $node->setAttribute('name', $this->getName());
        $node->setAttribute('url', $this->getId());
        $node->setAttribute('href', $this->getHref());
        return $node;
    }

    public function __toString(): string
    {
        return get_class($this) . ' ' . $this->getId();
    }
}

