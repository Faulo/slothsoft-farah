<?php
namespace Slothsoft\Farah\ResponseStrategy;

use Slothsoft\Farah\HTTPResponse;

interface ResponseStrategyInterface
{
    
    public function setResponse(HTTPResponse $response);
    
    public function getResponse(): HTTPResponse;
    
    public function process();
}

