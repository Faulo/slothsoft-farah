<?php
declare(strict_types = 1);

namespace Slothsoft\Farah\Http;

use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ServerRequest;
use GuzzleHttp\Psr7\Stream;
use GuzzleHttp\Psr7\Utils;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;
use RuntimeException;
use Slothsoft\Core\IO\HTTPFile;
use Slothsoft\Core\StreamWrapper\StreamWrapperInterface;
use Slothsoft\Farah\FarahUrl\FarahUrl;
use Throwable;

/**
 * Factory for PSR-7 requests, responses, streams, and Farah-specific request messages.
 *
 * @author Daniel Schulz
 * @since 2018-04-17
 */
abstract class MessageFactory {
    
    public static function createServerRequest(): ServerRequestInterface {
        return ServerRequest::fromGlobals();
    }
    
    public static function createCustomRequest(string $method, UriInterface $uri, $body = null): ServerRequestInterface {
        $serverRequest = new ServerRequest($method, $uri, [], $body, $_SERVER['SERVER_PROTOCOL'], $_SERVER);
        $params = [];
        parse_str($uri->getQuery(), $params);
        return $serverRequest->withQueryParams($params);
    }
    
    public static function createServerResponse(int $statusCode, array $headers, ?StreamInterface $stream = null, bool $isBufferable = true): ResponseInterface {
        return $isBufferable
            ? new Response($statusCode, $headers, $stream)
            : (new StreamingResponse($statusCode, $headers, $stream))->withHeader('X-Accel-Buffering', 'no');
    }
    
    public static function createStreamFromResource($resource): StreamInterface {
        return new Stream($resource);
    }
    
    public static function createStreamFromContents(string $contents): StreamInterface {
        $stream = self::createStreamFromResource(Utils::tryFopen('php://temp', StreamWrapperInterface::MODE_CREATE_READWRITE));
        try {
            if ($stream->write($contents) !== strlen($contents)) {
                throw new RuntimeException('Failed to write complete response contents to temporary stream.');
            }
            $stream->rewind();
            return $stream;
        } catch (Throwable $e) {
            $stream->close();
            throw $e;
        }
    }
    
    public static function createStreamFromFile(HTTPFile $file): StreamInterface {
        return self::createStreamFromResource(Utils::tryFopen($file->getPath(), StreamWrapperInterface::MODE_OPEN_READONLY));
    }
    
    public static function createStreamFromUrl(FarahUrl $url): StreamInterface {
        return self::createStreamFromResource(Utils::tryFopen((string) $url, StreamWrapperInterface::MODE_OPEN_READONLY));
    }
}
