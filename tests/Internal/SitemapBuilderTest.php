<?php
declare(strict_types = 1);

namespace Slothsoft\Farah\Internal;

use DOMDocument;
use DOMElement;
use DOMXPath;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Slothsoft\Core\DOMHelper;
use Slothsoft\Core\IO\Writable\DOMWriterInterface;
use Slothsoft\Farah\Exception\EmptySitemapException;
use Slothsoft\Farah\FarahUrl\FarahUrl;
use Slothsoft\Farah\Kernel;
use Slothsoft\Farah\Module\Asset\AssetInterface;
use Slothsoft\Farah\Module\Executable\ExecutableInterface;
use Slothsoft\Farah\Module\Result\ResultInterface;

/**
 * SitemapBuilderTest
 *
 * @see SitemapBuilder
 *
 */
final class SitemapBuilderTest extends TestCase {
    
    /**
     *
     * @test
     */
    private function createAsset(DOMWriterInterface $writer): AssetInterface {
        $result = $this->createMock(ResultInterface::class);
        $result->method('lookupDOMWriter')->willReturn($writer);
        $executable = $this->createMock(ExecutableInterface::class);
        $executable->method('lookupXmlResult')->willReturn($result);
        $asset = $this->createMock(AssetInterface::class);
        $asset->method('lookupExecutable')->willReturn($executable);
        $asset->method('createUrl')->willReturn(FarahUrl::createFromReference('farah://test@test/sitemap'));
        return $asset;
    }
    
    /** @return iterable<string, array{bool}> */
    public static function loadFailures(): iterable {
        yield 'empty document' => [false];
        yield 'writer exception' => [true];
    }
    
    /**
     * @dataProvider loadFailures
     * @runInSeparateProcess
     */
    public function testFailedLoadCanBeRetried(bool $throw): void {
        $document = new DOMDocument();
        $document->loadXML('<domain xmlns="' . DOMHelper::NS_FARAH_SITES . '" name="example.test"><page name="hello"/></domain>');
        $writer = $this->createMock(DOMWriterInterface::class);
        $attempts = 0;
        $writer->expects($this->exactly(2))->method('toDocument')->willReturnCallback(function () use (&$attempts, $document, $throw): DOMDocument {
            if (++$attempts === 1) {
                if ($throw) {
                    throw new RuntimeException('First load failed');
                }
                return new DOMDocument();
            }
            return $document;
        });
        Kernel::setCurrentSitemap($this->createAsset($writer));
        $builder = new SitemapBuilder();
        try {
            $builder->toDocument();
            $this->fail('The first sitemap load must fail.');
        } catch (RuntimeException $e) {
            if (! $throw) {
                $this->assertInstanceOf(EmptySitemapException::class, $e);
            }
        }
        $this->assertSame($document, $builder->toDocument());
        $builder->toDocument();
        $this->assertSame('http://example.test/hello/', (new DOMXPath($document))->evaluate('string(//*[@name="hello"]/@url)'));
    }
    
    /** @runInSeparateProcess */
    public function testMissingConfigurationBuildsDefaultSitemap(): void {
        Kernel::clearCurrentSitemap();
        $document = (new SitemapBuilder())->toDocument();
        $root = $document->documentElement;
        $this->assertInstanceOf(DOMElement::class, $root);
        $this->assertSame('localhost', $root->getAttribute('name'));
        $this->assertSame('/', $root->getAttribute('uri'));
    }
    
    public function testClassExists(): void {
        $this->assertTrue(class_exists(SitemapBuilder::class), "Failed to load class 'Slothsoft\Farah\Internal\SitemapBuilder'!");
    }
}