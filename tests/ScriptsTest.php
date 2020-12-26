<?php
declare(strict_types = 1);
namespace Slothsoft\Farah;

use PHPUnit\Framework\TestCase;

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
        $result = [];
        exec(sprintf('composer farah-asset %s', escapeshellarg($url)), $result);
        $result = implode("\n", $result) . "\n";
        $this->assertEquals(file_get_contents($url), $result);
    }
    
    public function someFarahPages(): array {
        return [
            [
                '/',
                '/sitemap/'
            ]
        ];
    }
    
    /**
     *
     * @dataProvider someFarahPages
     */
    public function testFarahPage(string $url): void {
        Kernel::setCurrentSitemap('farah://slothsoft@farah/example-domain');
        
        $result = [];
        exec(sprintf('composer farah-page %s', escapeshellarg($url)), $result);
        $result = implode("\n", $result) . "\n";
        $this->assertEquals(file_get_contents($url), $result);
    }
}