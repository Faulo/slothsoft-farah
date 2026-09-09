<?php
declare(strict_types = 1);

namespace Slothsoft\Farah\Internal;

use DOMDocument;
use PHPUnit\Framework\Constraint\IsEqual;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Slothsoft\Core\DOMHelper;
use Slothsoft\Farah\FarahUrl\FarahUrl;
use Slothsoft\Farah\FarahUrl\FarahUrlAuthority;
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

    private static function normalizeOpcacheStatistics(string $phpInfo): string {
        $statisticNames = [
            'Cache hits',
            'Cache misses',
            'Used memory',
            'Free memory',
            'Wasted memory',
            'Interned Strings Used memory',
            'Interned Strings Free memory',
            'Cached scripts',
            'Cached keys',
            'OOM restarts',
            'Hash keys restarts',
            'Manual restarts'
        ];
        $pattern = sprintf(
            '/^(%s) =&gt; .*$/m',
            implode('|', array_map('preg_quote', $statisticNames))
        );

        return preg_replace_callback(
            '/^Zend OPcache\R.*?^Manual restarts =&gt; [^\r\n]*$/ms',
            static function (array $matches) use ($pattern): string {
                return preg_replace($pattern, '$1 =&gt; [runtime value]', $matches[0]);
            },
            $phpInfo
        );
    }
    
    /**
     *
     * @dataProvider countProvider
     * @runInSeparateProcess
     */
    public function test_read(int $count): void {
        for ($i = 0; $i < $count; $i++) {
            $actual = file_get_contents(self::REFERENCE);
            $this->assertThat(
                self::normalizeOpcacheStatistics($actual),
                new IsEqual(self::normalizeOpcacheStatistics(self::getPhpInfo()))
            );
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
    
    /**
     * @dataProvider responseProvider
     */
    public function test_phpinfo_response(string $path, string $mimeType, string $fileName, bool $isHTML): void {
        $server = new FarahServer();
        $server->setModule(
            FarahUrlAuthority::createFromVendorAndModule('slothsoft', 'phpinfo-test'),
            'test-files/phpinfo-test'
        );
        $server->setSitemap(FarahUrl::createFromReference('farah://slothsoft@phpinfo-test/sitemap'));
        $server->start();

        try {
            [$source, $headers] = $this->request($server->uri . $path);

            $this->assertSame("$mimeType; charset=UTF-8", $headers['content-type']);
            $this->assertStringContainsString(sprintf('filename="%s"', $fileName), $headers['content-disposition']);

            $document = new DOMDocument();
            if ($isHTML) {
                $this->assertStringStartsWith('<!DOCTYPE html>', $source);
                $this->assertStringNotContainsString('<?xml', $source);
                $this->assertTrue($document->loadHTML($source), "Failed to parse HTML response from $path:" . PHP_EOL . $source);
                $title = $document->getElementsByTagName('title')->item(0)->textContent;
            } else {
                $this->assertStringStartsWith('<?xml', $source);
                $this->assertTrue($document->loadXML($source), "Failed to parse XHTML response from $path:" . PHP_EOL . $source);
                $this->assertSame(DOMHelper::NS_HTML, $document->documentElement->namespaceURI);
                $title = DOMHelper::loadXPath($document)->evaluate('string(//html:title)');
            }

            $this->assertThat($title, new IsEqual(sprintf('PHP %s - phpinfo()', PHP_VERSION)), "Failed to retrieve <title> from $path:" . PHP_EOL . $source);
        } finally {
            $server->quit();
        }
    }

    public function responseProvider(): iterable {
        yield 'asset XML' => [
            '/slothsoft@farah/phpinfo%23xml',
            'application/xhtml+xml',
            'phpinfo.xhtml',
            false
        ];
        yield 'asset HTML' => [
            '/slothsoft@farah/phpinfo%23html',
            'text/html',
            'phpinfo.html',
            true
        ];
        yield 'page XML' => [
            '/phpinfo-xml/',
            'application/xhtml+xml',
            'phpinfo.xhtml',
            false
        ];
        yield 'page HTML' => [
            '/phpinfo-html/',
            'text/html',
            'phpinfo.html',
            true
        ];
    }

    private function request(string $url): array {
        $source = file_get_contents($url);
        if (! is_string($source)) {
            throw new RuntimeException("Failed to retrieve $url.");
        }

        $headers = [];
        foreach ($http_response_header ?? [] as $header) {
            if (($separator = strpos($header, ':')) !== false) {
                $headers[strtolower(substr($header, 0, $separator))] = trim(substr($header, $separator + 1));
            }
        }
        return [
            $source,
            $headers
        ];
    }
}
