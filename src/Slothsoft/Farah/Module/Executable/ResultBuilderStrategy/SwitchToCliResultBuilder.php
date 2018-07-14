<?php
namespace Slothsoft\Farah\Module\Executable\ResultBuilderStrategy;

use Slothsoft\Core\ServerEnvironment;
use Slothsoft\Core\IO\Memory;
use Slothsoft\Core\IO\Writable\Delegates\ChunkWriterFromChunksDelegate;
use Slothsoft\Farah\FarahUrl\FarahUrlStreamIdentifier;
use Slothsoft\Farah\Module\Executable\ExecutableInterface;
use Slothsoft\Farah\Module\Result\ResultStrategies;
use Slothsoft\Farah\Module\Result\StreamBuilderStrategy\ChunkWriterStreamBuilder;
use Generator;

class SwitchToCliResultBuilder implements ResultBuilderStrategyInterface
{
    private $resultBuilder;
    private $fileName;
    
    public function __construct(ResultBuilderStrategyInterface $resultBuilder, string $fileName)
    {
        $this->resultBuilder = $resultBuilder;
        $this->fileName = $fileName;
    }
    
    public function buildResultStrategies(ExecutableInterface $context, FarahUrlStreamIdentifier $type): ResultStrategies
    {
        if (PHP_SAPI === 'cli') {
            return $this->resultBuilder->buildResultStrategies($context, $type);
        } else {
            $command = sprintf(
                '%s\\vendor\\bin\\farah-asset %s',
                ServerEnvironment::getRootDirectory(),
                escapeshellarg((string) $context->createUrl($type))
            );
            $delegate = function() use($command) : Generator {
                $stream = popen($command, 'rb');
                while (!feof($stream)) {
                    yield fread($stream, Memory::ONE_KILOBYTE);
                }
                pclose($stream);
            };
            $writer = new ChunkWriterFromChunksDelegate($delegate);
            $streamBuilder = new ChunkWriterStreamBuilder($writer, $this->fileName);
            return new ResultStrategies($streamBuilder);
        }
    }
}

