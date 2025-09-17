<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Sites;

use PHPUnit\Framework\TestCase;

/**
 * DomainTest
 *
 * @see Domain
 *
 * @todo auto-generated
 */
class DomainTest extends TestCase {
    
    public function testClassExists(): void {
        $this->assertTrue(class_exists(Domain::class), "Failed to load class 'Slothsoft\Farah\Sites\Domain'!");
    }
}