<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\RequestStrategy;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface RequestStrategyInterface {
    
    public function process(ServerRequestInterface $request): ResponseInterface;
}

