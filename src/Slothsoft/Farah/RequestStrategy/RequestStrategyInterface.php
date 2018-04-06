<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\RequestStrategy;

use Slothsoft\Farah\HTTPRequest;
use Slothsoft\Farah\Module\Results\ResultInterface;

interface RequestStrategyInterface
{

    public function setRequest(HTTPRequest $request);

    public function getRequest(): HTTPRequest;

    public function process() : ResultInterface;
}

