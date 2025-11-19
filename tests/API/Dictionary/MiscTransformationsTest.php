<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\API\Dictionary;

use Ds\Set;
use PHPUnit\Framework\TestCase;
use Slothsoft\Core\DOMHelper;
use Slothsoft\Farah\Dictionary;
use Slothsoft\FarahTesting\Constraints\DOMNodeEqualTo;
use Slothsoft\Farah\FarahUrl\FarahUrlAuthority;
use Slothsoft\Farah\Module\Module;
use Slothsoft\Farah\FarahUrl\FarahUrl;
use PHPUnit\Framework\Constraint\IsEqual;

final class MiscTransformationsTest extends TestCase {
    
    public function setUp(): void {
        Module::registerWithXmlManifestAndDefaultAssets(FarahUrlAuthority::createFromVendorAndModule('slothsoft', 'test-dictionary'), __DIR__ . '/../../../test-files/test-dictionary');
    }
    
    /**
     *
     * @runInSeparateProcess
     */
    public function test_currentLang(): void {
        $dict = Dictionary::getInstance();
        
        Dictionary::setSupportedLanguages('en');
        
        $this->assertThat($dict->getLang(), new IsEqual('en'));
    }
    
    /**
     *
     * @runInSeparateProcess
     * @dataProvider exampleProvider
     */
    public function test_linkingResult(string $inputUrl, string $dictionaryUrl, string $language, string $expectedUrl): void {
        Dictionary::setSupportedLanguages($language);
        
        $input = DOMHelper::loadDocument($inputUrl);
        
        $urls = new Set();
        $urls->add(FarahUrl::createFromReference($dictionaryUrl));
        
        $dict = Dictionary::getInstance();
        $dict->translateDocumentViaDictionary($input, $urls);
        
        $expected = DOMHelper::loadDocument($expectedUrl);
        
        $this->assertThat($input, new DOMNodeEqualTo($expected));
    }
    
    public function exampleProvider(): iterable {
        yield 'translate document' => [
            'farah://slothsoft@test-dictionary/documents/untranslated',
            'farah://slothsoft@test-dictionary/dictionary',
            'en',
            'farah://slothsoft@test-dictionary/documents/translated-en'
        ];
    }
}