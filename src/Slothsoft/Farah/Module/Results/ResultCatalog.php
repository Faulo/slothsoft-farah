<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Results;

use Slothsoft\Core\IO\HTTPFile;
use Slothsoft\Core\IO\Writable\DOMWriterInterface;
use Slothsoft\Core\IO\Writable\FileWriterInterface;
use Slothsoft\Farah\Exception\ResultTypeNotSupportedException;
use Slothsoft\Farah\Module\FarahUrl\FarahUrl;
use Slothsoft\Farah\Module\Results\Files\BinaryFileResult;
use Slothsoft\Farah\Module\Results\Files\TextFileResult;
use Slothsoft\Farah\Module\Results\Files\XmlFileResult;
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
                return self::createDOMWriterResult($url, $result);
            case $result instanceof FileWriterInterface:
                return self::createFileWriterResult($url, $result);
            case $result instanceof DOMDocument:
                return self::createDOMDocumentResult($url, $result);
            case $result instanceof DOMElement:
                return self::createDOMElementResult($url, $result);
            case $result instanceof Closure:
                return self::createClosureResult($url, $result);
            case is_object($result):
                throw new ResultTypeNotSupportedException(get_class($result));
            default:
                throw new ResultTypeNotSupportedException(gettype($result));
        }
    }
    public static function createNullResult(FarahUrl $url) : NullResult {
        return new NullResult($url);
    }
    public static function createFileWriterResult(FarahUrl $url, FileWriterInterface $writer) : FileWriterResult {
        return new FileWriterResult($url, $writer);
    }
    public static function createDOMWriterResult(FarahUrl $url, DOMWriterInterface $writer) : DOMWriterResult {
        return new DOMWriterResult($url, $writer);
    }
    public static function createTransformationResult(FarahUrl $url, string $name) : TransformationResult {
        return new TransformationResult($url, $name);
    }
    public static function createDOMDocumentResult(FarahUrl $url, DOMDocument $document) : DOMDocumentResult {
        return new DOMDocumentResult($url, $document);
    }
    public static function createDOMElementResult(FarahUrl $url, DOMElement $element) : DOMElementResult {
        return new DOMElementResult($url, $element);
    }
    public static function createBinaryFileResult(FarahUrl $url, HTTPFile $file) : BinaryFileResult{
        return new BinaryFileResult($url, $file);
    }
    public static function createTextFileResult(FarahUrl $url, HTTPFile $file) : TextFileResult{
        return new TextFileResult($url, $file);
    }
    public static function createXmlFileResult(FarahUrl $url, HTTPFile $file) : XmlFileResult {
        return new XmlFileResult($url, $file);
    }
    public static function createClosureResult(FarahUrl $url, Closure $closure) : NullResult {
        return new ClosureResult($url, $closure);
    }
}

