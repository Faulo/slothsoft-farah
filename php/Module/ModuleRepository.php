<?php

declare(strict_types = 1);
namespace Slothsoft\Farah\Module;

use Slothsoft\Farah\Kernel;
use Slothsoft\Farah\Exception\ExceptionContext;
use Throwable;

/**
 *
 * @author Daniel Schulz
 *        
 */
class ModuleRepository
{

    private $moduleList;

    public static function getInstance()
    {
        static $instance;
        if ($instance === null) {
            $instance = new ModuleRepository();
        }
        return $instance;
    }

    private function __construct()
    {
        $this->moduleList = [];
    }

    public function lookupModule(string $vendor, string $name): Module
    {
        $key = "$vendor@$name";
        if (! isset($this->moduleList[$key])) {
            $this->moduleList[$key] = new Module($vendor, $name);
            try {
                $this->moduleList[$key]->addEventAncestor(Kernel::getInstance()); // @TODO: öhh
                $this->moduleList[$key]->loadManifestFile();
            } catch (Throwable $exception) {
                throw ExceptionContext::append($exception, [
                    'module' => $this->moduleList[$key]
                ]);
            }
        }
        return $this->moduleList[$key];
    }

    public function lookupModuleByUrl(FarahUrl $url)
    {
        return $this->lookupModule($url->getVendor(), $url->getModule());
    }
}

