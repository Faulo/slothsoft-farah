<?php
declare(strict_types = 1);
namespace Slothsoft\Farah;

use PHPUnit\Framework\TestCase;

class ScriptsTest extends TestCase {

    public function someFarahUrls(): array {
        return [
            [
                'farah://slothsoft@farah/',
                'farah://slothsoft@core/'
            ]
        ];
    }

    /**
     *
     * @dataProvider someFarahUrls
     */
    public function testFarahAsset(string $url): void {
        $result = [];
        exec(sprintf('composer farah-asset %s', escapeshellarg($url)), $result);
        $result = implode("\n", $result) . "\n";
        $this->assertEquals(file_get_contents($url), $result);
    }
}