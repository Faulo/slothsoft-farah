<?php
declare(strict_types = 1);

namespace Slothsoft\Farah\Exception;

use RuntimeException;
use Throwable;

/**
 * Exception type for converting Farah failures into HTTP status responses.
 *
 * @author Daniel Schulz
 * @since 2018-03-19
 */
final class HttpStatusException extends RuntimeException {
    
    private $headers;
    
    public function __construct(string $message = '', int $code = 0, ?Throwable $previous = null, array $headers = []) {
        parent::__construct($message, $code, $previous);
        $this->setAdditionalHeaders($headers);
    }
    
    public function setAdditionalHeaders(array $headers): void {
        $this->headers = $headers;
    }
    
    public function getAdditionalHeaders(): array {
        return $this->headers;
    }
}

