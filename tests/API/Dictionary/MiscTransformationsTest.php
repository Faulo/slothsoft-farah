<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\API\Dictionary;

use Ds\Set;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Constraint\IsEqual;
use Slothsoft\Core\DOMHelper;
use Slothsoft\Farah\Dictionary;
use Slothsoft\FarahTesting\Constraints\DOMNodeEqualTo;
use Slothsoft\Farah\FarahUrl\FarahUrl;
use Slothsoft\Farah\FarahUrl\FarahUrlAuthority;
use Slothsoft\Farah\Module\Module;
use Slothsoft\Farah\Module\Executable\ResultBuilderStrategy\TransformationResultBuilder;

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
     * @dataProvider translateDocumentViaDictionaryProvider
     */
    public function test_translateDocumentViaDictionary(string $inputUrl, string $dictionaryUrl, string $language, string $expectedUrl): void {
        Dictionary::setSupportedLanguages($language);
        
        $input = DOMHelper::loadDocument($inputUrl);
        
        $urls = new Set();
        $urls->add(FarahUrl::createFromReference($dictionaryUrl));
        
        $dict = Dictionary::getInstance();
        $dict->translateDocumentViaDictionary($input, $urls);
        
        $expected = DOMHelper::loadDocument($expectedUrl);
        
        $this->assertThat($input, new DOMNodeEqualTo($expected));
    }
    
    public function translateDocumentViaDictionaryProvider(): iterable {
        yield 'translate document' => [
            'farah://slothsoft@test-dictionary/documents/test',
            'farah://slothsoft@test-dictionary/dictionary',
            'en',
            'farah://slothsoft@test-dictionary/documents/test-translated-en'
        ];
        
        yield 'translate utf8' => [
            'farah://slothsoft@test-dictionary/documents/utf8',
            'farah://slothsoft@test-dictionary/dictionary',
            'en',
            'farah://slothsoft@test-dictionary/documents/utf8-translated-en'
        ];
    }
    
    /**
     *
     * @runInSeparateProcess
     * @dataProvider autoTranslateProvider
     */
    public function test_autoTranslate(string $actualUrl, string $language, string $expectedUrl): void {
        TransformationResultBuilder::$translateDictionaryAlpha = false;
        
        Dictionary::setSupportedLanguages($language);
        
        $actual = DOMHelper::loadDocument($actualUrl);
        
        $expected = DOMHelper::loadDocument($expectedUrl);
        
        $this->assertThat($actual, new DOMNodeEqualTo($expected));
    }
    
    public function autoTranslateProvider(): iterable {
        yield 'translate document' => [
            'farah://slothsoft@test-dictionary/translations/test',
            'en',
            'farah://slothsoft@test-dictionary/documents/test-transformed-en'
        ];
        
        yield 'translate utf8' => [
            'farah://slothsoft@test-dictionary/translations/utf8',
            'en',
            'farah://slothsoft@test-dictionary/documents/utf8-transformed-en'
        ];
        
        yield 'lookup via xsl' => [
            'farah://slothsoft@test-dictionary/translations/dictionary',
            'en',
            'farah://slothsoft@test-dictionary/dictionary/en'
        ];
        
        yield 'lookup via element' => [
            'farah://slothsoft@test-dictionary/translations/lookup',
            'en',
            'farah://slothsoft@test-dictionary/dictionary/en'
        ];
    }
}