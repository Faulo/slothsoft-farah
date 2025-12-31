<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Internal;

use PHPUnit\Framework\TestCase;

/**
 * CLIDaemonServerTest
 *
 * @see CLIDaemonServer
 */
final class CLIDaemonServerTest extends TestCase {
    
    public function testServer(): void {
        $server = new CLIDaemonServer(0);
        foreach ($server->onMessage([
            PHP_BINARY,
            '--version'
        ]) as $type => $output) {
            switch ($type) {
                case CLIDaemonServer::STDOUT:
                    $this->assertStringContainsString(PHP_VERSION, $output);
                    break;
                case CLIDaemonServer::STDERR:
                    $this->assertStringContainsString('php', $output);
                    $this->assertStringContainsString('--version', $output);
                    break;
            }
        }
    }
}