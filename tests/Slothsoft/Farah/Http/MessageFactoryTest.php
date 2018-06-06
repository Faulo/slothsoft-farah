<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Http;

use Slothsoft\Farah\ModuleTests\AbstractTestCase;

class MessageFactoryTest extends AbstractTestCase
{

    public function testCreateServerRequest()
    {
        $_SERVER['REQUEST_URI'] = 'farah://slothsoft@farah/?a=b';
        $pageRequest = MessageFactory::createServerRequest($_SERVER, $_REQUEST, $_FILES);
        $this->assertEquals('a=b', $pageRequest->getUri()
            ->getQuery());
    }
}

