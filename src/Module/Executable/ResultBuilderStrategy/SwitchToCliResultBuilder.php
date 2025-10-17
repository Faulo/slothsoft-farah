<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Executable\ResultBuilderStrategy;

use Psr\Http\Message\StreamInterface;
use Slothsoft\Core\ServerEnvironment;
use Slothsoft\Core\IO\Psr7\ProcessStream;
use Slothsoft\Core\IO\Writable\Delegates\StreamWriterFromStreamDelegate;
use Slothsoft\Farah\FarahUrl\FarahUrlStreamIdentifier;
use Slothsoft\Farah\Module\Executable\ExecutableInterface;
use Slothsoft\Farah\Module\Result\ResultStrategies;
use Slothsoft\Farah\Module\Result\StreamBuilderStrategy\StreamWriterStreamBuilder;

class SwitchToCliResultBuilder implements ResultBuilderStrategyInterface {
    
    private $resultBuilder;
    
    private $fileName;
    
    public function __construct(ResultBuilderStrategyInterface $resultBuilder, string $fileName) {
        $this->resultBuilder = $resultBuilder;
        $this->fileName = $fileName;
    }
    
    public function isDifferentFromDefault(FarahUrlStreamIdentifier $type): bool {
        return false;
    }
    
    public function buildResultStrategies(ExecutableInterface $context, FarahUrlStreamIdentifier $type): ResultStrategies {
        if (PHP_SAPI === 'cli') {
            return $this->resultBuilder->buildResultStrategies($context, $type);
        } else {
            $command = sprintf('%s\\vendor\\bin\\farah-asset %s', ServerEnvironment::getRootDirectory(), escapeshellarg((string) $context->createUrl($type)));
            $delegate = function () use ($command): StreamInterface {
                return new ProcessStream($command);
            };
            $writer = new StreamWriterFromStreamDelegate($delegate);
            $streamBuilder = new StreamWriterStreamBuilder($writer, $this->fileName);
            return new ResultStrategies($streamBuilder);
        }
    }
}

