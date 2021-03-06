<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\ResponseStrategy;

use Psr\Http\Message\ResponseInterface;

interface ResponseStrategyInterface {

    public function process(ResponseInterface $response);
}

