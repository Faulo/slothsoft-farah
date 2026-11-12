<?php
declare(strict_types = 1);

namespace Slothsoft\Farah\Module\Executable\ResultBuilderStrategy\Files;

use DOMDocument;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Slothsoft\Farah\FarahUrl\FarahUrl;
use SplFileInfo;

/**
 * Base64FileResultBuilderTest
 *
 * @see Base64FileResultBuilder
 *
 */
final class Base64FileResultBuilderTest extends TestCase {
    
    /** @return iterable<string, array{string}> */
    public static function fileContents(): iterable {
        yield 'empty file' => [''];
        yield 'text file' => ['Hello <world> & goodbye'];
    }
    
    /** @dataProvider fileContents */
    public function testReadsFileContents(string $contents): void {
        $file = temp_file(__METHOD__);
        file_put_contents($file, $contents);
        try {
            $builder = new Base64FileResultBuilder(FarahUrl::createFromReference('farah://test@test'), new SplFileInfo($file));
            $element = $builder->toElement(new DOMDocument());
            $this->assertSame(base64_encode($contents), $element->textContent);
        } finally {
            unlink($file);
        }
    }
    
    public function testMissingFileThrows(): void {
        $file = temp_file(__METHOD__);
        $builder = new Base64FileResultBuilder(FarahUrl::createFromReference('farah://test@test'), new SplFileInfo($file));
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Unable to read contents of "' . $file . '".');
        $builder->toElement(new DOMDocument());
    }
    
    /**
     *
     * @test
     */
    public function testClassExists(): void {
        $this->assertTrue(class_exists(Base64FileResultBuilder::class), "Failed to load class 'Slothsoft\Farah\Module\Executable\ResultBuilderStrategy\Files\Base64FileResultBuilder'!");
    }
}