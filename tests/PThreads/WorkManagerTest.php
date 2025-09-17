<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\PThreads;

use PHPUnit\Framework\TestCase;

/**
 * WorkManagerTest
 *
 * @see WorkManager
 *
 * @todo auto-generated
 */
class WorkManagerTest extends TestCase {
    
    public function testClassExists(): void {
        $this->assertTrue(class_exists(WorkManager::class), "Failed to load class 'Slothsoft\Farah\PThreads\WorkManager'!");
    }
}