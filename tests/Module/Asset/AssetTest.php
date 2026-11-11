<?php
declare(strict_types = 1);

namespace Slothsoft\Farah\Module\Asset;

use PHPUnit\Framework\TestCase;
use Slothsoft\Farah\FarahUrl\FarahUrl;
use Slothsoft\Farah\Module\Module;

/**
 * AssetTest
 *
 * @see Asset
 *
 */
final class AssetTest extends TestCase {
    
    /** @runInSeparateProcess */
    public function testNumericQueryNamesSurviveParameterFiltering(): void {
        $url = FarahUrl::createFromReference('farah://slothsoft@farah/current-request?0=value&01=other');
        $executable = Module::resolveToExecutable($url);
        
        $this->assertSame('value', $executable->getUrlArguments()->get('0'));
        $this->assertSame('other', $executable->getUrlArguments()->get('01'));
        $this->assertSame('0=value&01=other', (string) $executable->getUrlArguments());
    }
    
    /** @runInSeparateProcess */
    public function testNumericQueryNamesCanBeRemovedByParameterFiltering(): void {
        $url = FarahUrl::createFromReference('farah://slothsoft@farah/font-face-generator?0=value');
        $executable = Module::resolveToExecutable($url);
        
        $this->assertFalse($executable->getUrlArguments()->has('0'));
    }
    
    /**
     *
     * @test
     */
    public function testClassExists(): void {
        $this->assertTrue(class_exists(Asset::class), "Failed to load class 'Slothsoft\Farah\Module\Asset\Asset'!");
    }
}
