<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Http;

use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ServerRequest;
use GuzzleHttp\Psr7\Stream;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Slothsoft\Core\IO\HTTPFile;
use Slothsoft\Core\StreamWrapper\StreamWrapperInterface;
use Slothsoft\Farah\Module\FarahUrl\FarahUrl;

abstract class MessageFactory
{

    public static function createServerRequest(): ServerRequestInterface
    {
        return ServerRequest::fromGlobals();
    }

    public static function createServerResponse(int $statusCode, array $headers, StreamInterface $stream = null): ResponseInterface
    {
        return new Response($statusCode, $headers, $stream);
    }

    public static function createStreamFromResource($resource): StreamInterface
    {
        return new Stream($resource);
    }

    public static function createStreamFromContents(string $contents): StreamInterface
    {
        $handle = fopen('php://temp', StreamWrapperInterface::MODE_CREATE_READWRITE);
        fwrite($handle, $contents);
        rewind($handle);
        return self::createStreamFromResource($handle);
    }

    public static function createStreamFromFile(HTTPFile $file): StreamInterface
    {
        return self::createStreamFromResource(fopen($file->getPath(), StreamWrapperInterface::MODE_OPEN_READONLY));
    }

    public static function createStreamFromUrl(FarahUrl $url): StreamInterface
    {
        return self::createStreamFromResource(fopen((string) $url, StreamWrapperInterface::MODE_OPEN_READONLY));
    }
}

