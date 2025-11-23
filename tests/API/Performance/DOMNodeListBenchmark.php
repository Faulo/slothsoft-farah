<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\API\Performance;

use PHPUnit\Framework\TestCase;
use Slothsoft\Core\DOMHelper;
use Closure;
use DOMNodeList;

/**
 * DOMNodeListBenchmark
 *
 * @see DOMNodeList
 */
final class DOMNodeListBenchmark extends TestCase {
    
    private const SET_SIZE = 10_000;
    
    private const ITERATIONS = 1_000;
    
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
        for ($i = 0; $i < $source->length; $i ++) {
            yield $source->item($i);
        }
    }
    
    private static function spread_DOMNodeList(DOMNodeList $source): array {
        return [
            ...$source
        ];
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
            for ($i = 0; $i < self::ITERATIONS; $i ++) {
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
            for ($i = 0; $i < self::ITERATIONS; $i ++) {
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
            for ($i = 0; $i < self::ITERATIONS; $i ++) {
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
}