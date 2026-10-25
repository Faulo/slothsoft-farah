<?php
declare(strict_types = 1);

namespace Slothsoft\Farah\RequestStrategy;

use DOMDocument;
use GuzzleHttp\Psr7\ServerRequest;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Slothsoft\Farah\Module\Executable\Executable;
use Slothsoft\Farah\Module\Module;
use Slothsoft\Farah\Sites\Domain;

/** Executable examples of using download exceptions to escape page rendering. */
final class HttpDownloadResponseTest extends TestCase {
    /** @noinspection HttpUrlsUsage */
    private function request(array $parameters, string $method = 'GET'): ResponseInterface {
        Module::registerWithXmlManifestAndDefaultAssets('test@http-downloads', dirname(__DIR__, 2) . '/test-files/http-downloads');
        $document = new DOMDocument();
        $document->loadXML('<domain xmlns="http://schema.slothsoft.net/farah/sitemap" name="localhost" vendor="test" module="http-downloads" ref="/page" uri="/" version="1.1" />');
        $request = (new ServerRequest($method, 'http://localhost/'))->withQueryParams($parameters);
        return (new LookupPageStrategy(new Domain($document), Executable::resultIsHtml()))->process($request);
    }
    
    /** @runInSeparateProcess */
    public function testOrdinaryArchiveRequestRendersTheEnclosingPage(): void {
        $response = $this->request([]);
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('text/html; charset=UTF-8', $response->getHeaderLine('Content-Type'));
        $this->assertStringStartsWith('inline;', $response->getHeaderLine('Content-Disposition'));
        $body = (string) $response->getBody();
        $this->assertStringContainsString('outer-layout', $body);
        $this->assertStringContainsString('Archive listing', $body);
        $this->assertStringContainsString('archive.bin', $body);
    }
    
    /**
     * @dataProvider downloadProvider
     * @runInSeparateProcess
     */
    public function testSelectedFileBypassesTheEnclosingPage(string $stage, string $action, string $method): void {
        $response = $this->request(['stage' => $stage, 'action' => $action], $method);
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('application/octet-stream; charset=UTF-8', $response->getHeaderLine('Content-Type'));
        $disposition = $action === 'show' ? 'inline' : 'attachment';
        $this->assertSame("$disposition; filename=\"archive.bin\"; filename*=UTF-8''archive.bin", $response->getHeaderLine('Content-Disposition'));
        $payload = "\0\xffarchive\n";
        $this->assertSame((string) strlen($payload), $response->getHeaderLine('Content-Length'));
        $this->assertSame($method === 'HEAD' ? '' : $payload, (string) $response->getBody());
        if ($stage === 'direct' && $method === 'GET') {
            $this->assertNotNull(DownloadArchiveBuilder::$selectedResult);
            $this->assertSame(DownloadArchiveBuilder::$selectedResult->lookupStreamWriter()->toStream(), $response->getBody());
        }
    }
    
    public function downloadProvider(): iterable {
        foreach (['asset', 'asset-from-result', 'result', 'direct'] as $stage) {
            foreach (['download', 'show'] as $action) {
                foreach (['GET', 'HEAD'] as $method) {
                    yield "$stage / $action / $method" => [$stage, $action, $method];
                }
            }
        }
    }
}
