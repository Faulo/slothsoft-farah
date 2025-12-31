<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Daemon;

use PHPUnit\Framework\TestCase;

/**
 * DaemonClientTest
 *
 * @see DaemonClient
 *
 * @todo auto-generated
 */
final class DaemonClientTest extends TestCase {
    
    public function testClassExists(): void {
        $this->assertTrue(class_exists(DaemonClient::class), "Failed to load class 'Slothsoft\Farah\Daemon\DaemonClient'!");
    }
}