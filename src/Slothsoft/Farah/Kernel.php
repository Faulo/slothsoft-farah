<?php
declare(strict_types = 1);
namespace Slothsoft\Farah;

use Ds\Map;
use Psr\Http\Message\ServerRequestInterface;
use Slothsoft\Core\Configuration\ConfigurationField;
use Slothsoft\Farah\Configuration\AssetConfigurationField;
use Slothsoft\Farah\Exception\ModuleNotFoundException;
use Slothsoft\Farah\Module\Module;
use Slothsoft\Farah\Module\FarahUrl\FarahUrlAuthority;
use Slothsoft\Farah\Module\Node\Asset\AssetInterface;
use Slothsoft\Farah\RequestStrategy\RequestStrategyInterface;
use Slothsoft\Farah\ResponseStrategy\ResponseStrategyInterface;
use OutOfBoundsException;


class Kernel
{
    public static function getInstance() : self
    {
        static $instance;
        if ($instance === null) {
            $instance = new self();
        }
        return $instance;
    }

    private static function currentSitemap(): ConfigurationField
    {
        static $field;
        if ($field === null) {
            $field = new AssetConfigurationField();
        }
        return $field;
    }

    public static function setCurrentSitemap($asset)
    {
        self::currentSitemap()->setValue($asset);
    }

    public static function getCurrentSitemap(): AssetInterface
    {
        return self::currentSitemap()->getValue();
    }

    private static function currentRequest(): ConfigurationField
    {
        static $field;
        if ($field === null) {
            $field = new ConfigurationField();
        }
        return $field;
    }

    public static function setCurrentRequest(ServerRequestInterface $request)
    {
        self::currentRequest()->setValue($request);
    }

    public static function getCurrentRequest(): ServerRequestInterface
    {
        return self::currentRequest()->getValue();
    }

    private static function trackingEnabled(): ConfigurationField
    {
        static $field;
        if ($field === null) {
            $field = new ConfigurationField(false);
        }
        return $field;
    }

    public static function setTrackingEnabled(bool $value)
    {
        self::trackingEnabled()->setValue($value);
    }

    public static function getTrackingEnabled(): bool
    {
        return self::trackingEnabled()->getValue();
    }

    private static function trackingExceptionUris()
    {
        static $field;
        if ($field === null) {
            $field = new ConfigurationField([]);
        }
        return $field;
    }

    public static function setTrackingExceptionUris(string ...$uriList)
    {
        self::trackingExceptionUris()->setValue($uriList);
    }

    public static function getTrackingExceptionUris(): array
    {
        return self::trackingExceptionUris()->getValue();
    }

    private $modules;
    private function __construct()
    {
        return $this->modules = new Map();
    }

    public function handle(RequestStrategyInterface $requestStrategy, ResponseStrategyInterface $responseStrategy, ServerRequestInterface $request)
    {
        self::setCurrentRequest($request);
        
        $response = $requestStrategy->process($request);
        $responseStrategy->process($response);
    }
    
    /**
     * @param FarahUrlAuthority|string $authority
     * @throws ModuleNotFoundException
     * @return Module
     */
    public function lookupModule($authority): Module
    {
        try {
            return $this->modules->get((string) $authority);
        } catch(OutOfBoundsException $e) {
            throw new ModuleNotFoundException((string) $authority, null, $e);
        }
        
    }
    /**
     * @param Module $module
     */
    public function registerModule(Module $module) {
        $this->modules->put((string) $module->getId(), $module);
    }
    
    private function getDefaultAuthority() : FarahUrlAuthority {
        return FarahUrlAuthority::createFromVendorAndModule('slothsoft', 'farah');
    }
}


