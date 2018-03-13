<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Results;

use Slothsoft\Core\IO\Writable\DOMWriterInterface;
use Slothsoft\Core\IO\Writable\FileWriterInterface;
use Slothsoft\Farah\Exception\ExceptionContext;
use Slothsoft\Farah\Module\FarahUrl\FarahUrl;
use Closure;
use DOMDocument;
use DOMElement;
use InvalidArgumentException;

/**
 *
 * @author Daniel Schulz
 *        
 */
class ResultCatalog
{

    public static function createFromMixed(FarahUrl $url, $result): ResultInterface
    {
        switch (true) {
            case $result instanceof ResultInterface:
                return $result;
            case $result instanceof DOMWriterInterface:
                return new DOMWriterResult($url, $result);
            case $result instanceof FileWriterInterface:
                return new FileWriterResult($url, $result);
            case $result instanceof DOMDocument:
                return new DOMDocumentResult($url, $result);
            case $result instanceof DOMElement:
                return new DOMElementResult($url, $result);
            case $result instanceof Closure:
                return self::createFromMixed($url, $result($url));
            case is_object($result):
                throw ExceptionContext::append(new InvalidArgumentException("Closure return type " . get_class($result) . " is not supported by this implementation."), [
                    'class' => __CLASS__
                ]);
            default:
                throw ExceptionContext::append(new InvalidArgumentException("Closure return type " . gettype($result) . " is not supported by this implementation."), [
                    'class' => __CLASS__
                ]);
        }
    }
}

