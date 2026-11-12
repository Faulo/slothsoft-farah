<?php
declare(strict_types = 1);

namespace Slothsoft\Farah\LinkDecorator;

use DOMDocument;
use DOMElement;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Slothsoft\Core\DOMHelper;
use Slothsoft\Farah\Exception\MalformedDocumentException;
use Slothsoft\Farah\FarahUrl\FarahUrl;

/**
 * DecoratorFactoryTest
 *
 * @see DecoratorFactory
 *
 */
final class DecoratorFactoryTest extends TestCase {
    
    /**
     *
     * @test
     */
    /** @return iterable<string, array{string, string|null}> */
    public static function namespaces(): iterable {
        yield 'Farah' => [DOMHelper::NS_FARAH_MODULE, null];
        yield 'HTML head' => [DOMHelper::NS_HTML, 'head'];
        yield 'HTML root fallback' => [DOMHelper::NS_HTML, null];
        yield 'SVG defs' => [DOMHelper::NS_SVG, 'defs'];
        yield 'SVG root fallback' => [DOMHelper::NS_SVG, null];
    }
    
    /** @dataProvider namespaces */
    public function testLinksUseExpectedContainer(string $namespace, ?string $container): void {
        $document = new DOMDocument();
        $root = $document->createElementNS($namespace, 'root');
        $document->appendChild($root);
        $target = $root;
        if ($container !== null) {
            $target = $document->createElementNS($namespace, $container);
            $root->appendChild($target);
        }
        $decorator = DecoratorFactory::createForDocument($document);
        $decorator->linkScripts(FarahUrl::createFromReference('farah://test@test/script'));
        $script = $target->lastChild;
        $this->assertInstanceOf(DOMElement::class, $script);
        $attribute = match ($namespace) {
            DOMHelper::NS_FARAH_MODULE => 'ref',
            DOMHelper::NS_HTML => 'src',
            default => 'href'
        };
        $this->assertSame($namespace === DOMHelper::NS_FARAH_MODULE ? 'farah://test@test/script' : '/test@test/script', $script->getAttribute($attribute));
    }
    
    /** @dataProvider namespaces */
    public function testDecoratorsRejectRootlessDocument(string $namespace, ?string $container): void {
        $decorator = DecoratorFactory::createForNamespace($namespace);
        $this->expectException(MalformedDocumentException::class);
        $decorator->setTarget(new DOMDocument());
    }
    
    public function testFactoryRejectsRootlessDocument(): void {
        $this->expectException(MalformedDocumentException::class);
        DecoratorFactory::createForDocument(new DOMDocument());
    }
    
    public function testFactoryRejectsElementWithoutOwnerDocument(): void {
        $this->expectException(InvalidArgumentException::class);
        DecoratorFactory::createForElement(new DOMElement('html', '', DOMHelper::NS_HTML));
    }
    
    public function testClassExists(): void {
        $this->assertTrue(class_exists(DecoratorFactory::class), "Failed to load class 'Slothsoft\Farah\LinkDecorator\DecoratorFactory'!");
    }
}