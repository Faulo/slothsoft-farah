<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Http;

abstract class StatusCodes
{
    const STATUS_OK = 200;
    
    const STATUS_NO_CONTENT = 204;
    
    const STATUS_PARTIAL_CONTENT = 206;
    
    const STATUS_MULTIPLE_CHOICES = 300;
    
    const STATUS_MOVED_PERMANENTLY = 301;
    
    const STATUS_SEE_OTHER = 303;
    
    const STATUS_NOT_MODIFIED = 304;
    
    const STATUS_TEMPORARY_REDIRECT = 307;
    
    const STATUS_PERMANENT_REDIRECT = 308;
    
    const STATUS_BAD_REQUEST = 400;
    
    const STATUS_UNAUTHORIZED = 401;
    
    const STATUS_NOT_FOUND = 404;
    
    const STATUS_METHOD_NOT_ALLOWED = 405;
    
    const STATUS_GONE = 410;
    
    const STATUS_PRECONDITION_FAILED = 412;
    
    const STATUS_REQUESTED_RANGE_NOT_SATISFIABLE = 416;
    
    const STATUS_INTERNAL_SERVER_ERROR = 500;
    
    const STATUS_NOT_IMPLEMENTED = 501;
    
    const STATUS_HTTP_VERSION_NOT_SUPPORTED = 505;
    
    const REASON_PHRASES = [
        self::STATUS_OK => 'OK',
        self::STATUS_NO_CONTENT => 'No Content',
        self::STATUS_PARTIAL_CONTENT => 'Partial Content',
        self::STATUS_MULTIPLE_CHOICES => 'Multiple Choices',
        self::STATUS_MOVED_PERMANENTLY => 'Moved Permanently',
        self::STATUS_SEE_OTHER => 'See Other',
        self::STATUS_NOT_MODIFIED => 'Not Modified',
        self::STATUS_TEMPORARY_REDIRECT => 'Temporary Redirect',
        self::STATUS_PERMANENT_REDIRECT => 'Permanent Redirect',
        self::STATUS_BAD_REQUEST => 'Bad Request',
        self::STATUS_UNAUTHORIZED => 'Unauthorized',
        self::STATUS_NOT_FOUND => 'Not Found',
        self::STATUS_METHOD_NOT_ALLOWED => 'Method Not Allowed',
        self::STATUS_GONE => 'Gone',
        self::STATUS_PRECONDITION_FAILED => 'Precondition Failed',
        self::STATUS_REQUESTED_RANGE_NOT_SATISFIABLE => 'Requested Range Not Satisfiable',
        self::STATUS_INTERNAL_SERVER_ERROR => 'Internal Server Error',
        self::STATUS_NOT_IMPLEMENTED => 'Not Implemented',
        self::STATUS_HTTP_VERSION_NOT_SUPPORTED => 'HTTP Version Not Supported'
    ];
    
    public static function getReasonPhrase(int $statusCode) : string {
        return self::REASON_PHRASES[$statusCode] ?? '';
    }
}

