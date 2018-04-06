<?php
namespace Slothsoft\Farah\ResponseStrategy;

class ReturnResponseStrategy extends ResponseStrategyImplementation
{
    public function process()
    {
        return $this->getResponse();
    }
}

