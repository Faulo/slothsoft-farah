<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module;

use Psr\Http\Message\StreamInterface;
use Slothsoft\Farah\Exception\ModuleNotFoundException;
use Slothsoft\Farah\FarahUrl\FarahUrl;
use Slothsoft\Farah\FarahUrl\FarahUrlAuthority;
use Slothsoft\Farah\Module\Asset\AssetInterface;
use Slothsoft\Farah\Module\Executable\ExecutableInterface;
use Slothsoft\Farah\Module\Manifest\Manifest;
use Slothsoft\Farah\Module\Manifest\ManifestContainer;
use Slothsoft\Farah\Module\Manifest\ManifestInterface;
use Slothsoft\Farah\Module\Manifest\ManifestStrategies;
use Slothsoft\Farah\Module\Manifest\AssetBuilderStrategy\DefaultAssetBuilder;
use Slothsoft\Farah\Module\Manifest\TreeLoaderStrategy\XmlTreeLoader;
use Slothsoft\Farah\Module\Result\ResultInterface;
use DOMDocument;
use OutOfBoundsException;

class Module
{

    private static function getInstance(): self
    {
        static $instance;
        if ($instance === null) {
            $instance = new Module();
        }
        return $instance;
    }

    /**
     *
     * @param FarahUrlAuthority|string $authority
     * @param string $assetDirectory
     * @param ManifestStrategies $strategies
     */
    public static function register($authority, string $assetDirectory, ManifestStrategies $strategies): void
    {
        if (is_string($authority)) {
            $authority = FarahUrlAuthority::createFromHttpAuthority($authority);
        }
        $module = static::getInstance();
        $manifest = $module->createManifest($authority, $assetDirectory, $strategies);
        $module->setManifest($authority, $manifest);
    }

    /**
     *
     * @param FarahUrlAuthority|string $authority
     * @param string $assetDirectory
     */
    public static function registerWithXmlManifestAndDefaultAssets($authority, string $assetDirectory): void
    {
        static::register($authority, $assetDirectory, new ManifestStrategies(new XmlTreeLoader(), new DefaultAssetBuilder()));
    }

    public static function resolveToManifest(FarahUrl $url): ManifestInterface
    {
        return static::getInstance()->getManifest($url->getAssetAuthority());
    }

    public static function resolveToAsset(FarahUrl $url): AssetInterface
    {
        return static::resolveToManifest($url)->lookupAsset($url->getAssetPath());
    }

    public static function resolveToExecutable(FarahUrl $url): ExecutableInterface
    {
        return static::resolveToAsset($url)->lookupExecutable($url->getArguments());
    }

    public static function resolveToResult(FarahUrl $url): ResultInterface
    {
        return static::resolveToExecutable($url)->lookupResult($url->getStreamIdentifier());
    }

    public static function resolveToDocument(FarahUrl $url): DOMDocument
    {
        return static::resolveToExecutable($url)->lookupXmlResult()->toDocument();
    }

    public static function resolveToStream(FarahUrl $url): StreamInterface
    {
        return static::resolveToResult($url)->lookupStream();
    }

    private $manifests;

    private function __construct()
    {
        $this->manifests = new ManifestContainer();
    }

    private function createManifest(FarahUrlAuthority $authority, string $assetDirectory, ManifestStrategies $strategies): ManifestInterface
    {
        return new Manifest($this, $authority, $assetDirectory, $strategies);
    }

    private function setManifest(FarahUrlAuthority $authority, ManifestInterface $manifest): void
    {
        $this->manifests->put($authority, $manifest);
    }

    private function getManifest(FarahUrlAuthority $authority): ManifestInterface
    {
        try {
            return $this->manifests->get($authority);
        } catch (OutOfBoundsException $e) {
            throw new ModuleNotFoundException((string) $authority, null, $e);
        }
    }
}

