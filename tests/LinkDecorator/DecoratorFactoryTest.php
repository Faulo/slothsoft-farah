<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\LinkDecorator;

use PHPUnit\Framework\TestCase;

/**
 * DecoratorFactoryTest
 *
 * @see DecoratorFactory
 *
 * @todo auto-generated
 */
final class DecoratorFactoryTest extends TestCase {
    
    public function testClassExists(): void {
        $this->assertTrue(class_exists(DecoratorFactory::class), "Failed to load class 'Slothsoft\Farah\LinkDecorator\DecoratorFactory'!");
    }
}