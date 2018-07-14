<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\ResponseStrategy;

use Psr\Http\Message\ResponseInterface;

class SendHeaderAndBodyStrategy extends ResponseStrategyBase
{

    public function process(ResponseInterface $response)
    {
        if (! headers_sent()) {
            $header = new SendHeaderStrategy();
            $header->process($response);
            flush();
            
            if ($response->getBody()) {
                $body = new SendBodyStrategy();
                $body->process($response);
                flush();
            }
        }
    }
}

