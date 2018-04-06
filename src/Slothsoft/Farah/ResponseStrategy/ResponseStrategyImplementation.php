<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\ResponseStrategy;

use Slothsoft\Farah\HTTPResponse;

abstract class ResponseStrategyImplementation implements ResponseStrategyInterface
{

    private $response;

    public function setResponse(HTTPResponse $response)
    {
        $this->response = $response;
    }

    public function getResponse(): HTTPResponse
    {
        return $this->response;
    }
}

