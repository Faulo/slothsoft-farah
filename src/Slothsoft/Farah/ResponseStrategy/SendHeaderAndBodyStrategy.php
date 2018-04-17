<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\ResponseStrategy;

use Psr\Http\Message\ResponseInterface;
use Slothsoft\Farah\Http\StatusCode;

class SendHeaderAndBodyStrategy extends ResponseStrategyBase
{
    public function process(ResponseInterface $response)
    {
        if (!headers_sent()) {
            $header = new SendHeaderStrategy();
            $header->process($response);
            
            flush();
            
            switch ($response->getStatusCode()) {
                case StatusCode::STATUS_NO_CONTENT:
                case StatusCode::STATUS_MULTIPLE_CHOICES:
                case StatusCode::STATUS_MOVED_PERMANENTLY:
                case StatusCode::STATUS_SEE_OTHER:
                case StatusCode::STATUS_NOT_MODIFIED:
                case StatusCode::STATUS_TEMPORARY_REDIRECT:
                case StatusCode::STATUS_PERMANENT_REDIRECT:
                    break;
                default:
                    $body = new SendBodyStrategy('php://output');
                    $body->process($response);
                    flush();
                    break;
            }
        }
    }
}

