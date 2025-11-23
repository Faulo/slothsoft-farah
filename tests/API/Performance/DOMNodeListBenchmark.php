<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\API\Performance;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Constraint\IsEqual;
use Slothsoft\Core\DOMHelper;
use Closure;
use DOMDocument;
use DOMNodeList;

/**
 * DOMNodeListBenchmark
 *
 * @see DOMNodeList
 */
final class DOMNodeListBenchmark extends TestCase {
    
    private const WARMUP = 100;
    
    private const ITERATIONS = 500;
    
    private static function foreach_DOMNodeList(DOMNodeList $source): array {
        $result = [];
        foreach ($source as $node) {
            $result[] = $node;
        }
        return $result;
    }
    
    private static function iterator_to_array_DOMNodeList(DOMNodeList $source): array {
        return iterator_to_array($source);
    }
    
    private static function generator_DOMNodeList(DOMNodeList $source): array {
        return iterator_to_array(self::yield_items($source));
    }
    
    private static function yield_items(DOMNodeList $source): iterable {
        for ($i = 0, $j = $source->length; $i < $j; $i ++) {
            yield $source->item($i);
        }
    }
    
    private static function spread_DOMNodeList(DOMNodeList $source): array {
        return [
            ...$source
        ];
    }
    
    private static function stub_DOMNodeList(DOMNodeList $source): array {
        return range(1, $source->length);
    }
    
    public function searchProvider(): iterable {
        yield 'all elements' => [
            '*'
        ];
        
        yield 'schema elements' => [
            '*',
            DOMHelper::NS_XSD
        ];
        
        yield 'versioning elements' => [
            '*',
            DOMHelper::NS_SCHEMA_VERSIONING
        ];
    }
    
    /**
     *
     * @dataProvider searchProvider
     */
    public function test_getElementsByTagName(string $tag, ?string $ns = null): void {
        $methods = [];
        $methods['stub'] = Closure::fromCallable([
            __CLASS__,
            'stub_DOMNodeList'
        ]);
        $methods['foreach'] = Closure::fromCallable([
            __CLASS__,
            'foreach_DOMNodeList'
        ]);
        $methods['iterator_to_array'] = Closure::fromCallable([
            __CLASS__,
            'iterator_to_array_DOMNodeList'
        ]);
        $methods['generator'] = Closure::fromCallable([
            __CLASS__,
            'generator_DOMNodeList'
        ]);
        
        $document = DOMHelper::loadDocument('farah://slothsoft@farah/schema/module/1.1');
        $source = $ns === null ? $document->getElementsByTagName($tag) : $document->getElementsByTagNameNS($ns, $tag);
        $this->assertNotNull($source);
        $expected = $source->length;
        
        $results = [];
        foreach ($methods as $id => $method) {
            for ($i = 0; $i < self::WARMUP; $i ++) {
                $method($source);
            }
            
            $start = hrtime(true);
            for ($i = 0; $i < self::ITERATIONS; $i ++) {
                assert(count($method($source)) === $expected);
            }
            $result = hrtime(true) - $start;
            
            $results[$id] = $result;
        }
        
        printf('%sDOMDocument::getElementsByTagName(%s) benchmark (%d nodes):%s', PHP_EOL, $tag, $expected, PHP_EOL);
        foreach ($results as $id => $result) {
            printf(' %s: %.2f ms%s', $id, $result / 1_000_000, PHP_EOL);
        }
    }
    
    /**
     *
     * @dataProvider searchProvider
     */
    public function test_evaluate(string $tag, ?string $ns = null): void {
        $methods = [];
        $methods['stub'] = Closure::fromCallable([
            __CLASS__,
            'stub_DOMNodeList'
        ]);
        $methods['spread'] = Closure::fromCallable([
            __CLASS__,
            'spread_DOMNodeList'
        ]);
        $methods['foreach'] = Closure::fromCallable([
            __CLASS__,
            'foreach_DOMNodeList'
        ]);
        $methods['iterator_to_array'] = Closure::fromCallable([
            __CLASS__,
            'iterator_to_array_DOMNodeList'
        ]);
        $methods['generator'] = Closure::fromCallable([
            __CLASS__,
            'generator_DOMNodeList'
        ]);
        
        $document = DOMHelper::loadDocument('farah://slothsoft@farah/schema/module/1.1');
        $xpath = DOMHelper::loadXPath($document);
        if ($ns === null) {
            $query = $tag === '*' ? '//*' : "//*:$tag";
        } else {
            $xpath->registerNamespace('test', $ns);
            $query = "//test:$tag";
        }
        $source = $xpath->evaluate($query);
        $this->assertNotNull($source);
        $expected = $source->length;
        
        $results = [];
        foreach ($methods as $id => $method) {
            for ($i = 0; $i < self::WARMUP; $i ++) {
                $method($source);
            }
            
            $start = hrtime(true);
            for ($i = 0; $i < self::ITERATIONS; $i ++) {
                assert(count($method($source)) === $expected);
            }
            $result = hrtime(true) - $start;
            
            $results[$id] = $result;
        }
        
        printf('%sDOMXPath::evaluate(%s) benchmark (%d nodes):%s', PHP_EOL, $query, $expected, PHP_EOL);
        foreach ($results as $id => $result) {
            printf(' %s: %.2f ms%s', $id, $result / 1_000_000, PHP_EOL);
        }
    }
    
    /**
     *
     * @dataProvider searchProvider
     */
    public function test_query(string $tag, ?string $ns = null): void {
        $methods = [];
        $methods['stub'] = Closure::fromCallable([
            __CLASS__,
            'stub_DOMNodeList'
        ]);
        $methods['spread'] = Closure::fromCallable([
            __CLASS__,
            'spread_DOMNodeList'
        ]);
        $methods['foreach'] = Closure::fromCallable([
            __CLASS__,
            'foreach_DOMNodeList'
        ]);
        $methods['iterator_to_array'] = Closure::fromCallable([
            __CLASS__,
            'iterator_to_array_DOMNodeList'
        ]);
        $methods['generator'] = Closure::fromCallable([
            __CLASS__,
            'generator_DOMNodeList'
        ]);
        
        $document = DOMHelper::loadDocument('farah://slothsoft@farah/schema/module/1.1');
        $xpath = DOMHelper::loadXPath($document);
        if ($ns === null) {
            $query = $tag === '*' ? '//*' : "//*:$tag";
        } else {
            $xpath->registerNamespace('test', $ns);
            $query = "//test:$tag";
        }
        $source = $xpath->query($query);
        $this->assertNotNull($source);
        $expected = $source->length;
        
        $results = [];
        foreach ($methods as $id => $method) {
            for ($i = 0; $i < self::WARMUP; $i ++) {
                $method($source);
            }
            
            $start = hrtime(true);
            for ($i = 0; $i < self::ITERATIONS; $i ++) {
                assert(count($method($source)) === $expected);
            }
            $result = hrtime(true) - $start;
            
            $results[$id] = $result;
        }
        
        printf('%sDOMXPath::query(%s) benchmark (%d nodes):%s', PHP_EOL, $query, $expected, PHP_EOL);
        foreach ($results as $id => $result) {
            printf(' %s: %.2f ms%s', $id, $result / 1_000_000, PHP_EOL);
        }
    }
    
    /**
     *
     * @dataProvider isLiveProvider
     */
    public function test_DOMNodeList_isLive(string $mode, bool $remove, int $expected): void {
        $document = new DOMDocument();
        $node = $document->createElement('root');
        $document->appendChild($node);
        
        switch ($mode) {
            case 'getElementsByTagName':
                $result = $document->getElementsByTagName('root');
                break;
            case 'evaluate':
                $result = DOMHelper::loadXPath($document)->evaluate('.//root', $document);
                break;
            case 'query':
                $result = DOMHelper::loadXPath($document)->query('.//root', $document);
                break;
        }
        
        if ($remove) {
            $document->removeChild($node);
        }
        
        $this->assertThat($result->length, new IsEqual($expected));
    }
    
    public function isLiveProvider(): iterable {
        yield 'getElementsByTagName, as-is' => [
            'getElementsByTagName',
            false,
            1
        ];
        
        yield 'getElementsByTagName, remove' => [
            'getElementsByTagName',
            true,
            0
        ];
        
        yield 'evaluate, as-is' => [
            'evaluate',
            false,
            1
        ];
        
        yield 'evaluate, remove' => [
            'evaluate',
            true,
            1
        ];
        
        yield 'query, as-is' => [
            'query',
            false,
            1
        ];
        
        yield 'query, remove' => [
            'query',
            true,
            1
        ];
    }
}