<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Asset;

use Ds\Set;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Constraint\LessThan;
use Slothsoft\Farah\FarahUrl\FarahUrl;

/**
 * LinkInstructionCollectionTest
 *
 * @see LinkInstructionCollection
 */
final class LinkInstructionCollectionTest extends TestCase {
    
    public function testClassExists(): void {
        $this->assertTrue(class_exists(LinkInstructionCollection::class), "Failed to load class 'Slothsoft\Farah\Module\Asset\LinkInstructionCollection'!");
    }
    
    private const SET_SIZE = 10_000;
    
    private const ITERATIONS = 1_000;
    
    public function test_performance_mergeSet(): void {
        $target = range(1, self::SET_SIZE);
        $source = new Set(range(1, self::SET_SIZE, 2));
        
        $spreadTotalNs = 0;
        $foreachTotalNs = 0;
        $unionTotalNs = 0;
        
        for ($i = 0; $i < self::ITERATIONS; $i ++) {
            $spreadTotalNs += $this->testWithSpread($target, $source);
            $foreachTotalNs += $this->testWithForeach($target, $source);
            $unionTotalNs += $this->testWithUnion($target, $source);
        }
        
        $spreadMs = $spreadTotalNs / 1_000_000;
        $foreachMs = $foreachTotalNs / 1_000_000;
        $unionMs = $unionTotalNs / 1_000_000;
        
        printf("\nDs\\Set merge benchmark:\n  spread:   %.2f ms\n  foreach:  %.2f ms\n  union:  %.2f ms\n", $spreadMs, $foreachMs, $unionMs);
        
        $this->assertThat($spreadMs, new LessThan($foreachMs), sprintf('Expected spread-merge (%.2f ms) to be faster than foreach-merge (%.2f ms).', $spreadMs, $foreachMs));
        $this->assertThat($unionMs, new LessThan($spreadMs), sprintf('Expected union-merge (%.2f ms) to be faster than spread-merge (%.2f ms).', $unionMs, $spreadMs));
    }
    
    private function testWithSpread(array $target, Set $source): float {
        $target = new Set($target);
        
        $start = hrtime(true);
        $target->add(...$source);
        return hrtime(true) - $start;
    }
    
    private function testWithForeach(array $target, Set $source): float {
        $target = new Set($target);
        
        $start = hrtime(true);
        foreach ($source as $value) {
            $target->add($value);
        }
        return hrtime(true) - $start;
    }
    
    private function testWithUnion(array $target, Set $source): float {
        $target = new Set($target);
        
        $start = hrtime(true);
        $target = $target->union($source);
        return hrtime(true) - $start;
    }
    
    public function test_performance_addUrls(): void {
        $urls = new Set();
        foreach (range(1, self::SET_SIZE) as $i) {
            $urls->add(FarahUrl::createFromReference('farah://test@test/' . $i));
        }
        
        $spreadTotalNs = 0;
        $iterableTotalNs = 0;
        
        for ($i = 0; $i < self::ITERATIONS; $i ++) {
            $spreadTotalNs += $this->testForeachWithSpread(...$urls);
            $iterableTotalNs += $this->testForeachWithIterable($urls);
        }
        
        $spreadMs = $spreadTotalNs / 1_000_000;
        $iterableMs = $iterableTotalNs / 1_000_000;
        
        printf("\nDs\\Set foreach benchmark:\n  spread:   %.2f ms\n  iterable:  %.2f ms\n", $spreadMs, $iterableMs);
        
        $this->assertThat($spreadMs, new LessThan($iterableMs), sprintf('Expected foreach-spread (%.2f ms) to be faster than foreach-iterable (%.2f ms).', $spreadMs, $iterableMs));
    }
    
    private function testForeachWithSpread(FarahUrl ...$urls): float {
        $start = hrtime(true);
        foreach ($urls as $url) {
            $url->hash();
        }
        return hrtime(true) - $start;
    }
    
    private function testForeachWithIterable(iterable $urls): float {
        $start = hrtime(true);
        foreach ($urls as $url) {
            $url->hash();
        }
        return hrtime(true) - $start;
    }
}