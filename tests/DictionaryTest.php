<?php
declare(strict_types = 1);

namespace Slothsoft\Farah;

use DOMDocument;
use DOMElement;
use PHPUnit\Framework\TestCase;
use Slothsoft\Farah\FarahUrl\FarahUrl;
use Slothsoft\Farah\Module\Module;

/**
 * DictionaryTest
 *
 * @see Dictionary
 */
class DictionaryTest extends TestCase {
    
    /** @runInSeparateProcess */
    public function testEmptyDocumentRemainsEmpty(): void {
        $dictionary = Dictionary::getInstance();
        $dictionary->setLang('en');
        $document = new DOMDocument();
        $this->assertSame(0, $dictionary->translateDoc($document, FarahUrl::createFromReference('farah://test@test')));
        $this->assertNull($document->documentElement);
    }
    
    /** @runInSeparateProcess */
    public function testNoLanguageHasEmptyXsltValue(): void {
        Dictionary::setSupportedLanguages();
        $this->assertNull(Dictionary::getInstance()->getLang());
        $this->assertSame('', Dictionary::xsltCurrentLanguage());
    }
    
    /** @runInSeparateProcess */
    public function testNoLanguagePreservesTextWithoutLoadingDictionaries(): void {
        Dictionary::setSupportedLanguages();
        $dictionary = Dictionary::getInstance();
        $dictionary->registerDictionary(FarahUrl::createFromReference('farah://missing@dictionary/dictionary'));
        $original = '  "Original" & <content>  ';
        $this->assertSame($original, Dictionary::xsltLookupText($original));
        $this->assertSame($original, $dictionary->lookupText($original));
        $this->assertSame($original, $dictionary->lookupFragment($original)->textContent);
    }
    
    /** @runInSeparateProcess */
    public function testNoLanguagePreservesOriginalFragmentInTargetDocument(): void {
        Dictionary::setSupportedLanguages();
        $source = new DOMDocument();
        $source->loadXML('<span title="keep">original <b>markup</b></span>');
        $root = $source->documentElement;
        $this->assertInstanceOf(DOMElement::class, $root);
        $target = new DOMDocument();
        $fragment = Dictionary::getInstance()->lookupFragment($root, ownerDoc: $target);
        $this->assertSame($target, $fragment->ownerDocument);
        $this->assertSame($source->saveXML($root), $target->saveXML($fragment));
        $this->assertSame($root, $source->documentElement);
    }
    
    /** @return iterable<string, array{bool, string}> */
    public static function languageSelection(): iterable {
        yield 'selected language' => [true, ''];
        yield 'explicit language without selection' => [false, 'en'];
    }
    
    /**
     * @runInSeparateProcess
     * @dataProvider languageSelection
     */
    public function testRegisteredDictionaryLookupWithLanguage(bool $selected, string $override): void {
        Module::registerWithXmlManifestAndDefaultAssets('slothsoft@test-dictionary', __DIR__ . '/../test-files/test-dictionary');
        Dictionary::setSupportedLanguages(...($selected ? ['en'] : []));
        $dictionary = Dictionary::getInstance();
        $dictionary->registerDictionary(FarahUrl::createFromReference('farah://slothsoft@test-dictionary/dictionary'));
        $this->assertSame('hello world!', Dictionary::xsltLookupText('input', language: $override));
        $this->assertSame($selected ? 'en' : null, $dictionary->getLang());
    }
    
    /** @runInSeparateProcess */
    public function testLegacyLookupsHonorExplicitLanguageWithoutSelection(): void {
        Module::registerWithXmlManifestAndDefaultAssets('slothsoft@test-module', __DIR__ . '/../test-files/test-module');
        Dictionary::setSupportedLanguages();
        $dictionary = Dictionary::getInstance();
        $this->assertSame('translation-of-test', $dictionary->lookupText('test', 'slothsoft@test-module', 'en-us'));
        $this->assertSame('translation-of-test', $dictionary->lookupFragment('test', 'slothsoft@test-module', 'en-us')->textContent);
        $this->assertNull($dictionary->getLang());
    }
    
    public function testClassExists(): void {
        $this->assertTrue(class_exists(Dictionary::class), "Failed to load class 'Slothsoft\Farah\Dictionary'!");
    }
    
    /**
     *
     * @dataProvider sanitationProvider
     */
    public function test_xsltSanitizeKey(string $input, string $expected): void {
        $actual = Dictionary::xsltSanitizeKey($input);
        
        $this->assertSame($expected, $actual);
        $this->assertSame($actual, Dictionary::xsltSanitizeKey($actual));
    }
    
    /** @runInSeparateProcess */
    public function testSanitizeKeyDiscardsMalformedUtf8WithoutSubstitution(): void {
        $substitute = mb_substitute_character();
        try {
            mb_substitute_character(ord('Z'));
            $sanitized = Dictionary::xsltSanitizeKey("\xFF0A\x80B.1\xC3");
            $this->assertSame('AB.1', $sanitized);
            $this->assertSame($sanitized, Dictionary::xsltSanitizeKey($sanitized));
            $this->assertSame('', Dictionary::xsltSanitizeKey("\xFF0\x80"));
            $this->assertSame(ord('Z'), mb_substitute_character());
        } finally {
            mb_substitute_character($substitute);
        }
    }
    
    /**
     * @return iterable<array{0: string, 1: string}>
     */
    public function sanitationProvider(): iterable {
        yield 'empty string' => [
            '',
            ''
        ];
        
        yield 'words stay' => [
            'abcöäüß',
            'abcöäüß'
        ];
        
        yield 'letters stay' => [
            'id023',
            'id023'
        ];
        
        yield 'whitespace go' => [
            "  abc ö \t ä \n ü \r ß  ",
            'abcöäüß'
        ];
        
        yield 'only .-_ stay' => [
            'id.-_:@$%&/\\+,;()[]{}?',
            'id.-_'
        ];
        
        yield 'kanji' => [
            '和　製　漢　字',
            '和製漢字'
        ];
        
        yield 'only letter at beginning' => [
            '0.-A',
            'A'
        ];
        
        yield 'only _ at beginning' => [
            '0.-_',
            '_'
        ];
        
        yield 'middle dot' => [
            'A·B',
            'A·B'
        ];
        
        yield 'name characters only after the start' => [
            "0-.·\u{0301}\u{203F}_A0-.·\u{0301}\u{203F}",
            "_A0-.·\u{0301}\u{203F}"
        ];
        
        yield 'colon is excluded even after the start' => [
            ':first:second',
            'firstsecond'
        ];
        
        yield 'XML name start characters outside the old letter category' => [
            "\u{200C}\u{2070}A",
            "\u{200C}\u{2070}A"
        ];
        
        yield 'supplementary range ends at U+EFFFF' => [
            "\u{F0000}\u{10000}A\u{EFFFF}\u{F0000}",
            "\u{10000}A\u{EFFFF}"
        ];
        
        yield 'name characters without a valid start are discarded' => [
            "0-.·\u{0301}\u{203F}: ",
            ''
        ];
    }
}
