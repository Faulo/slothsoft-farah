<?php
declare(strict_types = 1);

namespace Slothsoft\Farah\ResponseStrategy;

use RuntimeException;
use Slothsoft\Core\IO\Writable\Delegates\ChunkWriterFromChunksDelegate;
use Slothsoft\Farah\FarahUrl\FarahUrlArguments;
use Slothsoft\Farah\Module\Asset\AssetInterface;
use Slothsoft\Farah\Module\Asset\ExecutableBuilderStrategy\ExecutableBuilderStrategyInterface;
use Slothsoft\Farah\Module\Executable\ExecutableStrategies;
use Slothsoft\Farah\Module\Executable\ResultBuilderStrategy\ChunkWriterResultBuilder;

/** Test producer with an empty-history-style initial event and client-controlled progress. */
final class StreamingBuilder implements ExecutableBuilderStrategyInterface {
    
    public function buildExecutableStrategies(AssetInterface $context, FarahUrlArguments $args): ExecutableStrategies {
        $bufferable = isset($_GET['buffered']);
        $writer = new ChunkWriterFromChunksDelegate(static function () use ($bufferable): iterable {
            yield "event:start\ndata:\n\n";
            self::awaitClient('next', $bufferable);
            yield "event:next\ndata:second\n\n";
            self::awaitClient('finish', $bufferable);
            $gate = getenv('FARAH_STREAM_GATE');
            if ($gate) {
                touch($gate . '/completed');
            }
        });
        return new ExecutableStrategies(new ChunkWriterResultBuilder($writer, 'events.txt', $bufferable));
    }
    
    private static function awaitClient(string $step, bool $bufferable): void {
        $gate = getenv('FARAH_STREAM_GATE');
        if (! $gate || $bufferable || isset($_GET['blocked']) || isset($_GET['nested'])) {
            return;
        }
        $deadline = microtime(true) + 5;
        while (! is_file($gate . '/' . $step)) {
            if (microtime(true) >= $deadline) {
                throw new RuntimeException('Client did not release streaming producer: ' . $step);
            }
            usleep(10_000);
            clearstatcache();
        }
    }
}
