<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\StreamWrapper;

use PHPUnit\Framework\TestCase;

/**
 * FarahStreamWrapperFactoryTest
 *
 * @see FarahStreamWrapperFactory
 *
 * @todo auto-generated
 */
class FarahStreamWrapperFactoryTest extends TestCase {

    public function testClassExists(): void {
        $this->assertTrue(class_exists(FarahStreamWrapperFactory::class), "Failed to load class 'Slothsoft\Farah\StreamWrapper\FarahStreamWrapperFactory'!");
    }
}