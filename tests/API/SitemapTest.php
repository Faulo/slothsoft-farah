<?php
declare(strict_types = 1);

namespace Slothsoft\Farah\API;

use Slothsoft\Farah\Configuration\AssetConfigurationField;
use Slothsoft\Farah\Module\Asset\AssetInterface;
use Slothsoft\FarahTesting\Module\AbstractSitemapTest;

final class SitemapTest extends AbstractSitemapTest {
    
    protected static function loadSitesAsset(): AssetInterface {
        return (new AssetConfigurationField('farah://slothsoft@farah/example-domain'))->getValue();
    }
}