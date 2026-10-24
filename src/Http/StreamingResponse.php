<?php
declare(strict_types = 1);

namespace Slothsoft\Farah\Http;

use GuzzleHttp\Psr7\Response;

/**
 * Marks a response whose result explicitly forbids buffering.
 *
 * PSR-7 clones retain this marker, independently of body size and media type.
 * @internal
 */
final class StreamingResponse extends Response {
}
