<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Schema;

use PHPUnit\Framework\TestCase;

/**
 * SchemaLocatorTest
 *
 * @see SchemaLocator
 *
 * @todo auto-generated
 */
class SchemaLocatorTest extends TestCase {
    
    public function testClassExists(): void {
        $this->assertTrue(class_exists(SchemaLocator::class), "Failed to load class 'Slothsoft\Farah\Schema\SchemaLocator'!");
    }
}