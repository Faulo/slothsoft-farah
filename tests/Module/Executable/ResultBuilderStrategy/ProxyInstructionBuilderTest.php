<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Executable\ResultBuilderStrategy;

use PHPUnit\Framework\TestCase;

/**
 * ProxyInstructionBuilderTest
 *
 * @see ProxyInstructionBuilder
 *
 * @todo auto-generated
 */
class ProxyInstructionBuilderTest extends TestCase {
    
    public function testClassExists(): void {
        $this->assertTrue(class_exists(ProxyInstructionBuilder::class), "Failed to load class 'Slothsoft\Farah\Module\Executable\ResultBuilderStrategy\ProxyInstructionBuilder'!");
    }
}