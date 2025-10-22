<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\API;

use Slothsoft\FarahTesting\Module\AbstractModuleTest;
use Slothsoft\Farah\FarahUrl\FarahUrlAuthority;

class AssetsModuleTest extends AbstractModuleTest {
    
    protected static function getManifestAuthority(): FarahUrlAuthority {
        return FarahUrlAuthority::createFromVendorAndModule('slothsoft', 'farah');
    }
}