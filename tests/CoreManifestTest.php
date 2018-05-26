<?php
declare(strict_types = 1);

use Slothsoft\Farah\ModuleTests\AbstractXmlTreeLoaderTest;

class CoreManifestTest extends AbstractXmlTreeLoaderTest
{
    protected static function getManifestDirectory(): string
    {
        return dirname(__DIR__) . DIRECTORY_SEPARATOR . 'assets-core';
    }
    
}