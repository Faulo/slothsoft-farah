<?php
declare(strict_types = 1);

namespace Slothsoft\Farah\ResponseStrategy;

use Exception;
use PHPUnit\Framework\TestCase;
use Slothsoft\Core\IO\Writable\Adapter\StreamWriterFromChunkWriter;
use Slothsoft\Core\IO\Writable\Delegates\ChunkWriterFromChunksDelegate;
use Slothsoft\Farah\Http\MessageFactory;

/**
 * SendBodyStrategyTest
 *
 * @see SendBodyStrategy
 *
 */
final class SendBodyStrategyTest extends TestCase {
    
    /**
     *
     * @test
     */
    public function testClassExists(): void {
        $this->assertTrue(class_exists(SendBodyStrategy::class), "Failed to load class 'Slothsoft\Farah\ResponseStrategy\SendBodyStrategy'!");
    }
    
    /** @runInSeparateProcess */
    public function testBufferedOutputIsNotFlushed(): void {
        $response = MessageFactory::createServerResponse(200, [], MessageFactory::createStreamFromContents('body'));
        ob_start();
        try {
            echo 'prefix:';
            $level = ob_get_level();
            (new SendBodyStrategy())->process($response);
            $this->assertSame($level, ob_get_level());
            $this->assertSame('prefix:body', ob_get_contents());
        } finally {
            ob_end_clean();
        }
    }
    
    /**
     * @runInSeparateProcess
     * @throws Exception
     */
    public function testAlternateDestinationDoesNotChangeOutputBuffers(): void {
        $file = temp_file(__CLASS__);
        $response = MessageFactory::createServerResponse(200, [], MessageFactory::createStreamFromContents('body'), false);
        ob_start();
        try {
            echo 'prefix:';
            $level = ob_get_level();
            (new SendBodyStrategy($file))->process($response);
            $this->assertSame($level, ob_get_level());
            $this->assertSame('prefix:', ob_get_contents());
            $this->assertSame('body', file_get_contents($file));
        } finally {
            ob_end_clean();
        }
    }
    
    /**
     * @dataProvider bufferabilityProvider
     * @runInSeparateProcess
     */
    public function testDestinationSeesStreamingChunksBeforeProducerFinishes(bool $isBufferable): void {
        $this->assertTrue(stream_wrapper_register('farah-test-output', BufferedOutputStream::class));
        ob_start();
        try {
            echo 'unrelated output';
            $level = ob_get_level();
            $writer = new ChunkWriterFromChunksDelegate(function () use ($isBufferable): iterable {
                yield 'first';
                $this->assertSame($isBufferable ? '' : 'first', BufferedOutputStream::$published);
                yield 'second';
                $this->assertSame($isBufferable ? '' : 'firstsecond', BufferedOutputStream::$published);
            });
            $input = (new StreamWriterFromChunkWriter($writer, false))->toStream();
            $response = MessageFactory::createServerResponse(200, [], $input, $isBufferable);
            (new SendBodyStrategy('farah-test-output://destination'))->process($response);
            $this->assertSame('firstsecond', BufferedOutputStream::$published);
            $this->assertSame($level, ob_get_level());
            $this->assertSame('unrelated output', ob_get_contents());
        } finally {
            ob_end_clean();
            stream_wrapper_unregister('farah-test-output');
        }
    }
    
    public function bufferabilityProvider(): iterable {
        yield 'non-bufferable response flushes each chunk' => [false];
        yield 'bufferable response waits for stream closure' => [true];
    }
}
