<?php
namespace Slothsoft\Farah\ResponseStrategy;

class EchoResponseStrategy extends ResponseStrategyImplementation
{
    public function process()
    {
        $this->getResponse()->send();
    }
}

