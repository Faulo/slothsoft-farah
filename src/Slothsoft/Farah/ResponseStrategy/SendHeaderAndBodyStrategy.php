<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\ResponseStrategy;

use Psr\Http\Message\ResponseInterface;
use Slothsoft\Farah\Http\StatusCodes;

class SendHeaderAndBodyStrategy extends ResponseStrategyBase
{
    public function process(ResponseInterface $response)
    {
        if (!headers_sent()) {
            $header = new SendHeaderStrategy();
            $header->process($response);
            
            flush();
            
            switch ($response->getStatusCode()) {
                case StatusCodes::STATUS_NO_CONTENT:
                case StatusCodes::STATUS_MULTIPLE_CHOICES:
                case StatusCodes::STATUS_MOVED_PERMANENTLY:
                case StatusCodes::STATUS_SEE_OTHER:
                case StatusCodes::STATUS_NOT_MODIFIED:
                case StatusCodes::STATUS_TEMPORARY_REDIRECT:
                case StatusCodes::STATUS_PERMANENT_REDIRECT:
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

