<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Results;

use Slothsoft\Core\IO\Writable\DOMWriterInterface;
use Slothsoft\Core\IO\Writable\FileWriterInterface;
use Slothsoft\Farah\Exception\ResultTypeNotSupportedException;
use Slothsoft\Farah\Module\FarahUrl\FarahUrl;
use Closure;
use DOMDocument;
use DOMElement;

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
                throw new ResultTypeNotSupportedException(get_class($result));
            default:
                throw new ResultTypeNotSupportedException(gettype($result));
        }
    }
}

