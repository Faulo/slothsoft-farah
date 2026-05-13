<?php
declare(strict_types = 1);

namespace Slothsoft\Farah\Internal;

use DOMDocument;
use PHPUnit\Framework\Constraint\IsEqual;
use PHPUnit\Framework\TestCase;
use Slothsoft\Core\DOMHelper;
use Slothsoft\FarahTesting\Exception\BrowserDriverNotFoundException;
use Slothsoft\FarahTesting\FarahServer;

/**
 * PhpinfoBuilderTest
 *
 * @see PhpinfoBuilder
 */
final class PhpinfoBuilderTest extends TestCase {
    
    public function testClassExists(): void {
        $this->assertTrue(class_exists(PhpinfoBuilder::class), "Failed to load class 'Slothsoft\Farah\Internal\PhpinfoBuilder'!");
    }
    
    private const REFERENCE = 'farah://slothsoft@farah/phpinfo';
    
    private static function getPhpInfo(): string {
        ob_start();
        phpinfo();
        $data = ob_get_contents();
        ob_end_clean();
        
        return '<pre>\n' . htmlentities($data, ENT_XML1 | ENT_DISALLOWED, 'UTF-8') . '</pre>';
    }
    
    /**
     *
     * @dataProvider countProvider
     * @runInSeparateProcess
     */
    public function test_read(int $count): void {
        for ($i = 0; $i < $count; $i++) {
            $actual = file_get_contents(self::REFERENCE);
            $this->assertThat($actual, new IsEqual(self::getPhpInfo()));
        }
    }
    
    public function countProvider(): iterable {
        yield 'once' => [
            1
        ];
        yield 'twice' => [
            2
        ];
        yield 'thrice' => [
            3
        ];
    }
    
    public function test_phpinfo_xhtml() {
        $server = new FarahServer();
        $server->start();
        
        try {
            $source = file_get_contents("$server->uri/slothsoft@farah/phpinfo");
            
            $document = new DOMDocument();
            $actual = $document->loadXML($source);
            $this->assertTrue($actual, "Failed to retrieve /slothsoft@farah/phpinfo:" . PHP_EOL . $source);
            
            $xpath = DOMHelper::loadXPath($document);
            $actual = $xpath->evaluate('string(//html:title)');
            $this->assertThat($actual, new IsEqual(sprintf('PHP %s - phpinfo()', PHP_VERSION)), "Failed to retrieve <title> from /slothsoft@farah/phpinfo:" . PHP_EOL . $source);
        } catch (BrowserDriverNotFoundException $e) {
            $this->markTestSkipped();
        }
    }
}