<?php
declare(strict_types = 1);

namespace Slothsoft\Farah\Exception;

use DOMXPath;
use Exception;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;
use Slothsoft\Core\StreamWrapper\StreamWrapperFactoryInterface;
use Slothsoft\Core\StreamWrapper\StreamWrapperRegistrar;

/**
 * ExceptionContextTest
 *
 * @see ExceptionContext
 */
final class ExceptionContextTest extends TestCase {
    
    /**
     *
     * @test
     */
    public function testClassExists(): void {
        $this->assertTrue(class_exists(ExceptionContext::class), "Failed to load class 'Slothsoft\Farah\Exception\ExceptionContext'!");
    }
    
    /** @return iterable<string, array{bool, bool}> */
    public static function stylesheetAvailability(): iterable {
        yield 'available' => [true, false];
        yield 'missing' => [false, false];
        yield 'resolution failure' => [false, true];
    }
    
    /**
     * @dataProvider stylesheetAvailability
     * @runInSeparateProcess
     */
    public function testStylesheetAvailabilityUsesStatOnly(bool $exists, bool $throws): void {
        $factory = $this->createMock(StreamWrapperFactoryInterface::class);
        $factory->expects($this->never())->method('createStreamWrapper');
        $stat = $factory->expects($this->once())->method('statUrl')->with('farah://slothsoft@farah/xsl/error', $this->callback(static fn(int $flags): bool => ($flags & STREAM_URL_STAT_QUIET) !== 0));
        if ($throws) {
            $stat->willThrowException(new Exception('Unavailable stylesheet'));
        } else {
            $stat->willReturn($exists ? ['mode' => 0100444] : false);
        }
        (new ReflectionProperty(StreamWrapperRegistrar::class, 'factories'))->setValue(null, ['farah' => $factory]);
        clearstatcache();
        $document = ExceptionContext::append(new Exception('Original error'))->toDocument();
        $xpath = new DOMXPath($document);
        $this->assertSame($exists, $xpath->evaluate('boolean(/processing-instruction("xml-stylesheet"))'));
        $this->assertSame('Original error', $document->documentElement?->getAttribute('message'));
    }
    
    /** @runInSeparateProcess */
    public function testPublishedStylesheetIsAvailable(): void {
        $this->assertTrue(file_exists('farah://slothsoft@farah/xsl/error'));
        $document = ExceptionContext::append(new Exception('test'))->toDocument();
        $this->assertSame(true, (new DOMXPath($document))->evaluate('boolean(/processing-instruction("xml-stylesheet"))'));
    }
    
    public function testAppendReusesContext(): void {
        $exception = new Exception('test');
        
        $expected = ExceptionContext::append($exception, [
            'class' => self::class
        ]);
        $actual = ExceptionContext::append($exception);
        
        $this->assertSame($expected, $actual);
        $this->assertSame(self::class, $actual->getClass());
    }
}
