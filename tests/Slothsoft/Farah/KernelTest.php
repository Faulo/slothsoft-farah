<?php
declare(strict_types = 1);
namespace Slothsoft\Farah;

use PHPUnit\Framework\TestCase;
use Slothsoft\Farah\RequestStrategy\AssetRequestStrategy;
use Slothsoft\Farah\ResponseStrategy\EchoResponseStrategy;
use Slothsoft\Farah\ResponseStrategy\ReturnResponseStrategy;

class KernelTest extends TestCase {
    public function testIsThereAnySyntaxError(){
        $kernel = new Kernel(new AssetRequestStrategy(), new EchoResponseStrategy());
        $this->assertTrue($kernel instanceof Kernel);
    }
    public function testReturnResponseStrategy(){
        $kernel = new Kernel(new AssetRequestStrategy(), new ReturnResponseStrategy());
        $res = $kernel->processPath('farah://slothsoft@farah/');
        $this->assertInstanceOf(HTTPResponse::class, $res);
    }
  
}