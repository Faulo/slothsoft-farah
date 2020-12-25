<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\LinkDecorator;

use Slothsoft\Core\DOMHelper;
use Slothsoft\Farah\Exception\NamespaceNotSupportedException;
use DOMDocument;
use DOMElement;

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
        $decorator = self::createDecorator($ns);
        $decorator->setNamespace($ns);
        return $decorator;
    }

    private static function createDecorator(string $ns): LinkDecoratorInterface {
        switch ($ns) {
            case DOMHelper::NS_FARAH_MODULE:
                return new FarahDecorator($ns);
            case DOMHelper::NS_HTML:
                return new HtmlDecorator($ns);
                return new FarahDecorator($ns);
            case DOMHelper::NS_SVG:
                return new SvgDecorator($ns);
            default:
                throw new NamespaceNotSupportedException($ns);
        }
    }
}

