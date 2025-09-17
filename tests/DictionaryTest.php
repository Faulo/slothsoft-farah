<?php
declare(strict_types = 1);
namespace Slothsoft\Farah;

use PHPUnit\Framework\TestCase;

/**
 * DictionaryTest
 *
 * @see Dictionary
 *
 * @todo auto-generated
 */
class DictionaryTest extends TestCase {
    
    public function testClassExists(): void {
        $this->assertTrue(class_exists(Dictionary::class), "Failed to load class 'Slothsoft\Farah\Dictionary'!");
    }
}