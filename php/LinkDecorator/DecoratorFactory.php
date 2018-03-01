<?php
namespace Slothsoft\Farah\LinkDecorator;

use Slothsoft\Core\DOMHelper;
use DomainException;

/**
 *
 * @author Daniel Schulz
 *        
 */
class DecoratorFactory
{
    public static function createForNamespace(string $ns) : LinkDecoratorInterface
    {
        switch ($ns) {
            case DOMHelper::NS_FARAH_MODULE:
                return new FarahDecorator($ns);
            case DOMHelper::NS_HTML:
                return new HtmlDecorator($ns);
            default:
                throw new DomainException("This implementation does not support <sfm:use-stylesheet> and <sfm:use-script> for XML namespace '$ns'.");
        }
    }
}

