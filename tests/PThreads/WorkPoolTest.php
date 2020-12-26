<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\PThreads;

use PHPUnit\Framework\TestCase;

/**
 * WorkPoolTest
 *
 * @see WorkPool
 *
 * @todo auto-generated
 */
class WorkPoolTest extends TestCase {

    public function testClassExists(): void {
        $this->assertTrue(class_exists(WorkPool::class), "Failed to load class 'Slothsoft\Farah\PThreads\WorkPool'!");
    }
}