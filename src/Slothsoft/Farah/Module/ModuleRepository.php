<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module;

use Slothsoft\Farah\Exception\ModuleNotFoundException;
use Slothsoft\Farah\Module\FarahUrl\FarahUrl;
use Slothsoft\Farah\Module\FarahUrl\FarahUrlAuthority;

/**
 *
 * @author Daniel Schulz
 *        
 */
class ModuleRepository
{

    private $registeredModules = [];

    public static function getInstance(): ModuleRepository
    {
        static $instance;
        if ($instance === null) {
            $instance = new ModuleRepository();
        }
        return $instance;
    }

    private function __construct()
    {}

    public function registerModule(Module $module)
    {
        $this->registeredModules[(string) $module->getAuthority()] = $module;
    }

    public function lookupModuleByAuthority(FarahUrlAuthority $authority): Module
    {
        $key = (string) $authority;
        if (! isset($this->registeredModules[$key])) {
            throw new ModuleNotFoundException($key);
        }
        return $this->registeredModules[$key];
    }

    public function lookupModuleByUrl(FarahUrl $url): Module
    {
        return $this->lookupModuleByAuthority($url->getAuthority());
    }

    public function lookupModule(string $vendor, string $name): Module
    {
        return $this->lookupModuleByAuthority(FarahUrlAuthority::createFromVendorAndModule($vendor, $module));
    }
}

