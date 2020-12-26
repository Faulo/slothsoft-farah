<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Internal;

use PHPUnit\Framework\TestCase;

/**
 * PhpinfoBuilderTest
 *
 * @see PhpinfoBuilder
 *
 * @todo auto-generated
 */
class PhpinfoBuilderTest extends TestCase {

    public function testClassExists(): void {
        $this->assertTrue(class_exists(PhpinfoBuilder::class), "Failed to load class 'Slothsoft\Farah\Internal\PhpinfoBuilder'!");
    }
}