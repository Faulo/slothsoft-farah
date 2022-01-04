<?php
declare(strict_types = 1);
namespace Slothsoft\Farah;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

class ScriptsTest extends TestCase {

    public function someFarahAssets(): array {
        return [
            [
                'farah://slothsoft@farah/',
                'farah://slothsoft@core/'
            ]
        ];
    }

    /**
     *
     * @dataProvider someFarahAssets
     */
    public function testFarahAsset(string $url): void {
        $process = Process::fromShellCommandline(sprintf('composer farah-asset %s', escapeshellarg($url)));
        $process->run();
        $result = $process->getOutput();
        $this->assertEquals(file_get_contents($url), $result);
    }
}