<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Exception;

class HttpStatusException extends \RuntimeException
{
    private $headers;
    public function __construct(string $message = '', int $code = 0, \Throwable $previous = null, array $headers = []) {
        parent::__construct($message, $code, $previous);
        $this->setAdditionalHeaders($headers);
    }
    public function setAdditionalHeaders(array $headers) {
        $this->headers = $headers;
    }
    public function getAdditionalHeaders() : array {
        return $this->headers;
    }
}

