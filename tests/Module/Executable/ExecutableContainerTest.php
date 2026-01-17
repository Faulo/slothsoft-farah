<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Executable;

use PHPUnit\Framework\TestCase;

/**
 * ExecutableContainerTest
 *
 * @see ExecutableContainer
 *
 * @todo auto-generated
 */
class ExecutableContainerTest extends TestCase {
    
    public function testClassExists(): void {
        $this->assertTrue(class_exists(ExecutableContainer::class), "Failed to load class 'Slothsoft\Farah\Module\Executable\ExecutableContainer'!");
    }
}