<?php
declare(strict_types = 1);

use Slothsoft\Farah\FarahUrl\FarahUrlAuthority;
use Slothsoft\Farah\ModuleTests\AbstractManifestTest;

class CoreModuleTest extends AbstractManifestTest
{
    protected static function getManifestAuthority() : FarahUrlAuthority {
        return FarahUrlAuthority::createFromVendorAndModule('slothsoft', 'core');
    }
}