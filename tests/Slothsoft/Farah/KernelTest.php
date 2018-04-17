<?php
declare(strict_types = 1);
namespace Slothsoft\Farah;

use PHPUnit\Framework\TestCase;

class KernelTest extends TestCase {
    public function testIsThereAnySyntaxError(){
        $this->assertInstanceOf(Kernel::class, new Kernel());
    }
}