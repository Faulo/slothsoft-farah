<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Http;

use Slothsoft\Farah\ModuleTests\AbstractTestCase;

class MessageFactoryTest extends AbstractTestCase
{
    public function testCreateServerRequest() {
        $_SERVER['REQUEST_URI'] .= '?a=b';
        $pageRequest = MessageFactory::createServerRequest($_SERVER, $_REQUEST, $_FILES);
        $this->assertEquals(['a' => 'b'], $pageRequest->getQueryParams());
        
        $_SERVER['REQUEST_URI'] = 'farah://slothsoft@farah?c=d';
        $assetRequest = MessageFactory::createServerRequest($_SERVER, $_REQUEST, $_FILES);
        $this->assertEquals(['c' => 'd'], $assetRequest->getQueryParams());
        
        $clonedRequest = $assetRequest->withQueryParams($assetRequest->getQueryParams() + ['e' => 'f']);
        $this->assertEquals(['c' => 'd', 'e' => 'f'], $clonedRequest->getQueryParams());
        
    }
}

