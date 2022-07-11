<?php
declare(strict_types = 1);
namespace Slothsoft\Farah;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

class ScriptsTest extends TestCase {

    public function someFarahAssets(): array {
        return [
            'farah://slothsoft@farah/' => [
                'farah://slothsoft@farah/'
            ],
            'farah://slothsoft@core/' => [
                'farah://slothsoft@core/'
            ]
        ];
    }

    /**
     *
     * @dataProvider someFarahAssets
     */
    public function testFarahAsset(string $url): void {
        $output = [];
        $code = 0;
        exec('composer', $output, $code);
        if ($code !== 0) {
            $this->markTestSkipped('Composer is not available in PATH, skipping test.');
        }
        
        $process = new Process([
            'composer',
            '-d',
            dirname(__DIR__),
            'exec',
            'farah-asset',
            $url
        ]);

        $code = $process->run();
        $result = $process->getOutput();
        $errors = $process->getErrorOutput();
        
        $this->assertEquals('', $errors, 'Calling composer failed! Command:' . PHP_EOL . $process->getCommandLine());
        
        $this->assertEquals(0, $code, 'Calling composer failed! Command:' . PHP_EOL . $process->getCommandLine());

        $this->assertEquals(file_get_contents($url), $result, 'Calling composer failed! Command:' . PHP_EOL . $process->getCommandLine());
    }
}