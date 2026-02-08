<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Internal;

use PHPUnit\Framework\TestCase;
use Slothsoft\FarahTesting\TestUtils;
use Slothsoft\Farah\Module\Module;
use Slothsoft\FarahTesting\Constraints\FileEqualsTextFile;

/**
 * FontFaceBuilderTest
 *
 * @see FontFaceBuilder
 */
final class FontFaceBuilderTest extends TestCase {
    
    public static function setUpBeforeClass(): void {
        TestUtils::changeWorkingDirectoryToComposerRoot();
        Module::registerWithXmlManifestAndDefaultAssets('slothsoft@test', 'test-files/test-fonts');
    }
    
    public function testClassExists(): void {
        $this->assertTrue(class_exists(FontFaceBuilder::class), "Failed to load class 'Slothsoft\Farah\Internal\FontFaceBuilder'!");
    }
    
    /**
     *
     * @runInSeparateProcess
     */
    public function test_generator(): void {
        $this->assertThat('farah://slothsoft@test/actual', new FileEqualsTextFile('farah://slothsoft@test/expected'));
    }
}