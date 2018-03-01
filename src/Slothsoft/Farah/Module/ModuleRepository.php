<?php

declare(strict_types = 1);
namespace Slothsoft\Farah\Module;

use Slothsoft\Farah\Kernel;
use Slothsoft\Farah\Exception\ExceptionContext;
use Slothsoft\Farah\Module\FarahUrl\FarahUrl;
use Throwable;
use Slothsoft\Farah\Module\FarahUrl\FarahUrlAuthority;

/**
 *
 * @author Daniel Schulz
 *        
 */
class ModuleRepository
{

    private $moduleList = [];

    public static function getInstance() : ModuleRepository
    {
        static $instance;
        if ($instance === null) {
            $instance = new ModuleRepository();
        }
        return $instance;
    }

    private function __construct()
    {
    }
    
    public function lookupModuleByAuthority(FarahUrlAuthority $authority)
    {
        $key = (string) $authority;
        if (! isset($this->moduleList[$key])) {
            $this->moduleList[$key] = $this->createModule($authority);
            //must register in $moduleList first to ensure recursive calls work out
            $this->loadModuleManifest($this->moduleList[$key]);
        }
        return $this->moduleList[$key];
    }
    public function lookupModuleByUrl(FarahUrl $url)
    {
        return $this->lookupModuleByAuthority($url->getAuthority());
    }
    public function lookupModule(string $vendor, string $name): Module
    {
        return $this->lookupModuleByAuthority(FarahUrlAuthority::createFromVendorAndModule($vendor, $module));
    }
    
    
    private function createModule(FarahUrlAuthority $authority) : Module {
        return new Module($authority);
    }
    private function loadModuleManifest(Module $module) {
        try {
            $module->addEventAncestor(Kernel::getInstance()); // @TODO: öhh
            $module->loadManifestFile();
        } catch (Throwable $exception) {
            throw ExceptionContext::append($exception, [
                'module' => $module,
                'class' => __CLASS__,
            ]);
        }
    }
}

