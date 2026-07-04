<?php
declare(strict_types = 1);

namespace Slothsoft\Farah\ResponseStrategy;

use Psr\Http\Message\ResponseInterface;

/**
 * Response strategy that emits only response headers.
 *
 * @author Daniel Schulz
 * @since 2018-04-17
 */
final class SendHeaderStrategy implements ResponseStrategyInterface {
    
    public function process(ResponseInterface $response) {
        $httpHeader = sprintf('%s/%d.%d %d %s', 'HTTP', 1, 1, $response->getStatusCode(), $response->getReasonPhrase());
        header($httpHeader, true, $response->getStatusCode());
        foreach (array_keys($response->getHeaders()) as $name) {
            header("$name: {$response->getHeaderLine($name)}");
        }
    }
}

