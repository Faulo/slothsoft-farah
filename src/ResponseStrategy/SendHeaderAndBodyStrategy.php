<?php
declare(strict_types = 1);

namespace Slothsoft\Farah\ResponseStrategy;

use Psr\Http\Message\ResponseInterface;

/**
 * Response strategy that emits headers followed by the response body.
 *
 * @author Daniel Schulz
 * @since 2018-04-17
 */
final class SendHeaderAndBodyStrategy implements ResponseStrategyInterface {
    
    public function process(ResponseInterface $response): void {
        if (! headers_sent()) {
            $header = new SendHeaderStrategy();
            $header->process($response);
            
            $body = new SendBodyStrategy();
            $body->process($response);
        }
    }
}
