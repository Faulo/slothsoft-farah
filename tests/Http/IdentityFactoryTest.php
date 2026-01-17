<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Http;

use PHPUnit\Framework\TestCase;

/**
 * IdentityFactoryTest
 *
 * @see IdentityFactory
 *
 * @todo auto-generated
 */
final class IdentityFactoryTest extends TestCase {
    
    public function testClassExists(): void {
        $this->assertTrue(class_exists(IdentityFactory::class), "Failed to load class 'Slothsoft\Farah\Http\IdentityFactory'!");
    }
}