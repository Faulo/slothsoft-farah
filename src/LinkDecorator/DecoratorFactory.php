<?php
declare(strict_types = 1);

namespace Slothsoft\Farah\LinkDecorator;

use DOMDocument;
use DOMElement;
use Slothsoft\Core\DOMHelper;
use Slothsoft\Farah\Exception\NamespaceNotSupportedException;

/**
 *
 * @author Daniel Schulz
 *
 */
class DecoratorFactory {
    
    public static function createForDocument(DOMDocument $targetDocument): LinkDecoratorInterface {
        $decorator = self::createForNamespace((string) $targetDocument->documentElement->namespaceURI);
        $decorator->setTarget($targetDocument);
        return $decorator;
    }
    
    public static function createForElement(DOMElement $node): LinkDecoratorInterface {
        $decorator = self::createForNamespace((string) $node->namespaceURI);
        $decorator->setTarget($node->ownerDocument);
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

