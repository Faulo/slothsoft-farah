<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Daemon;

use PHPUnit\Framework\TestCase;

/**
 * AbstractDaemonServerTest
 *
 * @see AbstractDaemonServer
 *
 * @todo auto-generated
 */
final class AbstractDaemonServerTest extends TestCase {
    
    public function testClassExists(): void {
        $this->assertTrue(class_exists(AbstractDaemonServer::class), "Failed to load class 'Slothsoft\Farah\Daemon\AbstractDaemonServer'!");
    }
}