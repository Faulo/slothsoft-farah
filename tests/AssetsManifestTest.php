<?php
declare(strict_types = 1);
namespace Slothsoft\Farah;

use Slothsoft\FarahTesting\Module\AbstractXmlManifestTest;

class AssetsManifestTest extends AbstractXmlManifestTest {
    
    protected static function getManifestDirectory(): string {
        return dirname(__DIR__) . DIRECTORY_SEPARATOR . 'assets';
    }
}