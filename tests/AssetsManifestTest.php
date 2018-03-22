<?php
namespace tests;

use Slothsoft\Farah\ModuleTests\XmlManifestTest;
use Slothsoft\Farah\Module\Manifest\XmlManifest;
use PHPUnit\Framework\TestCase;
use Slothsoft\Farah\Module\Manifest\ManifestInterface;

class AssetsManifestTest extends XmlManifestTest
{
    public function getManifest() : ManifestInterface {
        return new XmlManifest(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'assets.xml');
    }
}