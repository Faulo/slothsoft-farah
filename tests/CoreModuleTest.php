<?php
declare(strict_types = 1);
namespace Slothsoft\Farah;

use Slothsoft\Farah\FarahUrl\FarahUrlAuthority;
use Slothsoft\Farah\ModuleTests\AbstractModuleTest;

class CoreModuleTest extends AbstractModuleTest {

    protected static function getManifestAuthority(): FarahUrlAuthority {
        return FarahUrlAuthority::createFromVendorAndModule('slothsoft', 'core');
    }
}