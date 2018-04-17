<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Http;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;
use Psr\Http\Message\UriInterface;
use Slim\Http\Cookies;
use Slim\Http\Headers;
use Slim\Http\HeadersInterface;
use Slim\Http\Request;
use Slim\Http\RequestBody;
use Slim\Http\Response;
use Slim\Http\Stream;
use Slim\Http\UploadedFile;
use Slim\Http\Uri;
use Slothsoft\Core\IO\HTTPFile;
use Slothsoft\Farah\Module\FarahUrl\FarahUrl;
use Slothsoft\Core\StreamWrapper\StreamWrapperInterface;

abstract class MessageFactory
{
    public static function createServerRequest(array $_server = [], array $_request = [], array $_files = []) : ServerRequestInterface {
        $method = $_server['REQUEST_METHOD'] ?? null;
        
        $uri = static::createUri($_server);
        $headers = static::createHeadersFromGlobals($_server);
        $cookies = static::createCookiesFromHeader($headers->get('Cookie', []));
        $body = static::createRequestBody();
        $uploadedFiles = static::createUploadedFiles($_files);
        
        $request = new Request($method, $uri, $headers, $cookies, $_server, $body, $uploadedFiles);
        
        if ($method === 'POST' &&
            in_array($request->getMediaType(), ['application/x-www-form-urlencoded', 'multipart/form-data'])
            ) {
                $request = $request->withParsedBody($_request);
            }
            return $request;
    }
    private static function createUri(array $_server) : UriInterface {
        return Uri::createFromGlobals($_server);
    }
    private static function createHeadersFromGlobals(array $_server) : HeadersInterface {
        return Headers::createFromGlobals($_server);
    }
    private static function createHeadersFromMap(array $headers) : HeadersInterface {
        $ret = new Headers();
        foreach ($headers as $key => $val) {
            $ret->set($key, $val);
        }
        return $ret;
    }
    private static function createRequestBody() : StreamInterface {
        return new RequestBody();
    }
    private static function createCookiesFromHeader(array $header) : array {
        return Cookies::parseHeader($header);
    }
    private static function createUploadedFiles(array $files) : array {
        $parsed = [];
        foreach ($files as $field => $uploadedFile) {
            if (is_array($uploadedFile['error'])) {
                $parsed[$field] = [];
                foreach ($uploadedFile['error'] as $i => $tmp) {
                    $file = [];
                    foreach ($uploadedFile as $key => $data) {
                        $file[$key] = $data[$i];
                    }
                    $parsed[$field][] = self::createUploadedFile($file);
                }
            } else {
                $parsed[$field] = static::createUploadedFile($files);
            }
        }
        return $parsed;
    }
    private static function createUploadedFile(array $file) : UploadedFileInterface {
        return new UploadedFile(
            $uploadedFile['tmp_name'],
            $uploadedFile['name'] ?? null,
            $uploadedFile['type'] ?? null,
            $uploadedFile['size'] ?? null,
            $uploadedFile['error'] ?? null,
            true
        );
    }
    
    
    
    public static function createServerResponse(int $statusCode, array $headers, StreamInterface $stream) : ResponseInterface {
        return new Response(
            $statusCode,
            self::createHeadersFromMap($headers),
            $stream
        );
    }
    
    public static function createStreamFromResource($resource) : StreamInterface {
        return new Stream($resource);
    }
    public static function createStreamFromContents(string $contents) : StreamInterface {
        $handle = fopen('php://temp', StreamWrapperInterface::MODE_CREATE_READWRITE);
        fwrite($handle, $contents);
        rewind($handle);
        return self::createStreamFromResource($handle);
    }
    public static function createStreamFromFile(HTTPFile $file) : StreamInterface {
        return self::createStreamFromResource(fopen($file->getPath(), StreamWrapperInterface::MODE_OPEN_READONLY));
    }
    public static function createStreamFromUrl(FarahUrl $url) : StreamInterface {
        return self::createStreamFromResource(fopen((string) $url, StreamWrapperInterface::MODE_OPEN_READONLY));
    }
}

