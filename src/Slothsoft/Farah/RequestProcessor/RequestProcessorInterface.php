<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\RequestProcessor;

use Slothsoft\Farah\HTTPRequest;
use Slothsoft\Farah\HTTPResponse;

interface RequestProcessorInterface
{

    public function setRequest(HTTPRequest $request);

    public function getRequest(): HTTPRequest;

    public function setResponse(HTTPResponse $response);

    public function getResponse(): HTTPResponse;

    public function process();
}

