<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\API\Domain;

use PHPUnit\Framework\TestCase;
use Slothsoft\Core\DOMHelper;
use Slothsoft\Farah\Kernel;
use Slothsoft\FarahTesting\TestUtils;
use Slothsoft\FarahTesting\Constraints\DOMNodeEqualTo;
use Slothsoft\Farah\Http\MessageFactory;
use Slothsoft\Farah\Module\Module;
use Slothsoft\Farah\Module\Manifest\Manifest;
use Slothsoft\Farah\RequestStrategy\LookupPageStrategy;
use DOMDocument;

final class ParamTest extends TestCase {
    
    public static function setUpBeforeClass(): void {
        TestUtils::changeWorkingDirectoryToComposerRoot();
        Module::registerWithXmlManifestAndDefaultAssets('slothsoft@test.slothsoft.net', 'test-files/test-domain');
        Kernel::setCurrentSitemap('farah://slothsoft@test.slothsoft.net/sitemap');
    }
    
    private static function requestPage(string $path): string {
        $_SERVER['REQUEST_URI'] = $path;
        
        if ($query = parse_url($path, PHP_URL_QUERY)) {
            $args = [];
            parse_str($query, $args);
            foreach ($args as $key => $value) {
                $_GET[$key] = $value;
            }
        }
        
        $request = MessageFactory::createServerRequest($_SERVER, $_REQUEST, $_FILES);
        
        Kernel::setCurrentRequest($request);
        
        $lookup = new LookupPageStrategy();
        return (string) $lookup->process($request)->getBody();
    }
    
    /**
     *
     * @dataProvider pageProvider
     * @runInSeparateProcess
     */
    public function test_lookupTestPage(string $path, string $content) {
        $dom = new DOMHelper();
        $expected = $dom->parse($content);
        
        $actual = new DOMDocument();
        $actual->loadXML(self::requestPage($path));
        foreach ($actual->getElementsByTagNameNS(DOMHelper::NS_FARAH_MODULE, Manifest::TAG_REQUEST_INFO) as $node) {
            $actual = $node;
            break;
        }
        
        $this->assertThat($actual, new DOMNodeEqualTo($expected));
    }
    
    public function pageProvider(): iterable {
        yield '/' => [
            '/',
            <<<EOT
<domain xmlns="http://schema.slothsoft.net/farah/sitemap" xmlns:sfm="http://schema.slothsoft.net/farah/module" name="test.slothsoft.net" vendor="slothsoft" module="farah" ref="/current-sitemap" status-active="" status-public="" version="1.1" title="test.slothsoft.net" uri="/" url="http://test.slothsoft.net/" current="1">
    <file name="request" ref="/current-request" status-active="" status-public="" title="request" uri="/request" url="http://test.slothsoft.net/request">
    </file>
    <file name="request-with-param" ref="/current-request" status-active="" status-public="" title="request-with-param" uri="/request-with-param" url="http://test.slothsoft.net/request-with-param">
        <sfm:param name="param" value="value"/>
    </file>
    <file name="request-with-query" ref="/current-request?param=value" status-active="" status-public="" title="request-with-query" uri="/request-with-query" url="http://test.slothsoft.net/request-with-query">
        <sfm:param name="param" value="should not see this"/>
    </file>
    
    <file name="page" ref="//slothsoft@test.slothsoft.net/page" status-active="" status-public="" title="page" uri="/page" url="http://test.slothsoft.net/page">
        <sfm:param name="param" value="value"/>
    </file>
    <file name="page-with-param" ref="//slothsoft@test.slothsoft.net/page-with-query" status-active="" status-public="" title="page-with-param" uri="/page-with-param" url="http://test.slothsoft.net/page-with-param">
        <sfm:param name="param" value="override"/>
    </file>
    <file name="page-with-query" ref="//slothsoft@test.slothsoft.net/page-with-query" status-active="" status-public="" title="page-with-query" uri="/page-with-query" url="http://test.slothsoft.net/page-with-query">
    </file>
</domain>
EOT
        ];
        
        yield '/request' => [
            '/request',
            <<<EOT
<request-info xmlns="http://schema.slothsoft.net/farah/module" href="http://localhost/request" url="farah://slothsoft@farah/current-request" version="1.1"/>
EOT
        ];
        
        yield '/request?param=value' => [
            '/request?param=value',
            <<<EOT
<request-info xmlns="http://schema.slothsoft.net/farah/module" href="http://localhost/request?param=value" url="farah://slothsoft@farah/current-request?param=value" version="1.1">
    <param name="param" value="value"/>
</request-info>
EOT
        ];
        
        yield '/request-with-param' => [
            '/request-with-param',
            <<<EOT
<request-info xmlns="http://schema.slothsoft.net/farah/module" href="http://localhost/request-with-param" url="farah://slothsoft@farah/current-request?param=value" version="1.1">
    <param name="param" value="value"/>
</request-info>
EOT
        ];
        
        yield '/request-with-param?param=override' => [
            '/request-with-param?param=override',
            <<<EOT
<request-info xmlns="http://schema.slothsoft.net/farah/module" href="http://localhost/request-with-param?param=override" url="farah://slothsoft@farah/current-request?param=override" version="1.1">
    <param name="param" value="override"/>
</request-info>
EOT
        ];
        
        yield '/request-with-query' => [
            '/request-with-query',
            <<<EOT
<request-info xmlns="http://schema.slothsoft.net/farah/module" href="http://localhost/request-with-query" url="farah://slothsoft@farah/current-request?param=value" version="1.1">
    <param name="param" value="value"/>
</request-info>
EOT
        ];
        
        yield '/request-with-query?param=override' => [
            '/request-with-query?param=override',
            <<<EOT
<request-info xmlns="http://schema.slothsoft.net/farah/module" href="http://localhost/request-with-query?param=override" url="farah://slothsoft@farah/current-request?param=value" version="1.1">
    <param name="param" value="value"/>
</request-info>
EOT
        ];
        
        yield '/page' => [
            '/page',
            <<<EOT
<request-info xmlns="http://schema.slothsoft.net/farah/module" href="http://localhost/page" url="farah://slothsoft@test.slothsoft.net/page?param=value" version="1.1">
    <param name="param" value="value"/>
</request-info>
EOT
        ];
        
        yield '/page-with-param' => [
            '/page-with-param',
            <<<EOT
<request-info xmlns="http://schema.slothsoft.net/farah/module" href="http://localhost/page-with-param" url="farah://slothsoft@test.slothsoft.net/page-with-query?param=override" version="1.1">
    <param name="param" value="override"/>
</request-info>
EOT
        ];
    }
}