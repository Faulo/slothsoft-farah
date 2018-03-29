<?php
namespace tests;

use Slothsoft\Farah\Module\Module;
use Slothsoft\Farah\ModuleTests\AbstractModuleTest;
use Slothsoft\Farah\Module\FarahUrl\FarahUrlAuthority;
use Slothsoft\Farah\Module\Manifest\XmlManifest;

class CoreModuleTest extends AbstractModuleTest
{
    protected static function loadModule() : Module {
        return new Module(
            FarahUrlAuthority::createFromVendorAndModule('slothsoft', 'core'),
            dirname(__DIR__) . DIRECTORY_SEPARATOR . 'assets-core'
        );
    }
}