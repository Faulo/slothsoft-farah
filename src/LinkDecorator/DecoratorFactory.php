<?php
declare(strict_types = 1);

namespace Slothsoft\Farah\LinkDecorator;

use DOMDocument;
use DOMElement;
use InvalidArgumentException;
use Slothsoft\Core\DOMHelper;
use Slothsoft\Farah\Exception\MalformedDocumentException;
use Slothsoft\Farah\Exception\NamespaceNotSupportedException;

/**
 * Factory for selecting link decorators by DOM namespace.
 *
 * @author Daniel Schulz
 * @since 2018-03-01
 */
final class DecoratorFactory {
    
    public static function createForDocument(DOMDocument $targetDocument): LinkDecoratorInterface {
        $root = $targetDocument->documentElement;
        if ($root === null) {
            throw new MalformedDocumentException($targetDocument->documentURI ?? '');
        }
        $decorator = self::createForNamespace((string) $root->namespaceURI);
        $decorator->setTarget($targetDocument);
        return $decorator;
    }
    
    public static function createForElement(DOMElement $node): LinkDecoratorInterface {
        $document = $node->ownerDocument;
        if ($document === null) {
            throw new InvalidArgumentException('Link decorator elements must belong to a document.');
        }
        $decorator = self::createForNamespace((string) $node->namespaceURI);
        $decorator->setTarget($document);
        return $decorator;
    }
    
    public static function createForNamespace(string $ns): LinkDecoratorInterface {
        return match ($ns) {
            DOMHelper::NS_FARAH_MODULE => new FarahDecorator(),
            DOMHelper::NS_HTML => new HtmlDecorator(),
            DOMHelper::NS_SVG => new SvgDecorator(),
            default => throw new NamespaceNotSupportedException($ns),
        };
    }
}

