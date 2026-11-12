<?php
declare(strict_types = 1);

namespace Slothsoft\Farah\Http;

use GuzzleHttp\Psr7\Utils;
use LogicException;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamInterface;
use Slothsoft\Core\IO\Psr7\ZlibFilteredStreamFactory;
use Slothsoft\Core\IO\Writable\FilteredStreamWriterInterface;

/** @see ConfigurableCoding */
final class ConfigurableCodingTest extends TestCase {
    
    /** @return iterable<string, array{string, bool}> */
    public static function missingFilters(): iterable {
        yield 'encoding not configured' => ['encoding', false];
        yield 'encoding cleared' => ['encoding', true];
        yield 'decoding not configured' => ['decoding', false];
        yield 'decoding cleared' => ['decoding', true];
    }
    
    /** @dataProvider missingFilters */
    public function testMissingFilterThrowsWithoutReadingInput(string $direction, bool $clear): void {
        $coding = new ConfigurableCoding('custom');
        $set = 'set' . ucfirst($direction) . 'Filter';
        $remove = 'clear' . ucfirst($direction) . 'Filter';
        $has = 'has' . ucfirst($direction) . 'Filter';
        $operation = $direction === 'encoding' ? 'encodeStream' : 'decodeStream';
        if ($clear) {
            $filter = $this->createMock(FilteredStreamWriterInterface::class);
            $filter->expects($this->never())->method('toFilteredStream');
            $coding->$set($filter);
            $coding->$remove();
        }
        $this->assertFalse($coding->$has());
        $input = $this->createMock(StreamInterface::class);
        $input->expects($this->never())->method('read');
        $input->expects($this->never())->method('getContents');
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('No ' . $direction . ' filter is configured for "custom".');
        $coding->$operation($input);
    }
    
    public function testConfiguredFilterIsUsedAndCanBeReplaced(): void {
        $coding = new ConfigurableCoding('custom');
        $input = Utils::streamFor('input');
        $output = Utils::streamFor('filtered');
        $filter = $this->createMock(FilteredStreamWriterInterface::class);
        $filter->expects($this->once())->method('toFilteredStream')->with($this->identicalTo($input))->willReturn($output);
        $coding->setEncodingFilter($filter);
        $this->assertTrue($coding->hasEncodingFilter());
        $this->assertSame($output, $coding->encodeStream($input));
        $coding->clearEncodingFilter();
        $replacement = $this->createMock(FilteredStreamWriterInterface::class);
        $replacement->expects($this->once())->method('toFilteredStream')->with($this->identicalTo($input))->willReturn($input);
        $coding->setEncodingFilter($replacement);
        $this->assertSame($input, $coding->encodeStream($input));
    }
    
    public function testEncodingAndDecodingRemainIndependent(): void {
        $coding = new ConfigurableCoding('custom');
        $input = Utils::streamFor('input');
        $output = Utils::streamFor('decoded');
        $encoder = $this->createMock(FilteredStreamWriterInterface::class);
        $encoder->expects($this->never())->method('toFilteredStream');
        $decoder = $this->createMock(FilteredStreamWriterInterface::class);
        $decoder->expects($this->once())->method('toFilteredStream')->with($this->identicalTo($input))->willReturn($output);
        $coding->setEncodingFilter($encoder);
        $coding->setDecodingFilter($decoder);
        $coding->clearEncodingFilter();
        $this->assertFalse($coding->hasEncodingFilter());
        $this->assertTrue($coding->hasDecodingFilter());
        $this->assertSame($output, $coding->decodeStream($input));
    }
    
    public function testConfiguredGzipStillProducesValidCompressedContent(): void {
        $coding = new ConfigurableCoding('gzip');
        $coding->setEncodingFilter(new ZlibFilteredStreamFactory(ZLIB_ENCODING_GZIP));
        $this->assertSame('content', gzdecode($coding->encodeStream(Utils::streamFor('content'))->getContents()));
    }
}
