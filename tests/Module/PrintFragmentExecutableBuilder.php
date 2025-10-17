<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module;

use Slothsoft\Core\IO\Writable\Delegates\StringWriterFromStringDelegate;
use Slothsoft\Farah\FarahUrl\FarahUrlArguments;
use Slothsoft\Farah\FarahUrl\FarahUrlStreamIdentifier;
use Slothsoft\Farah\Module\Asset\AssetInterface;
use Slothsoft\Farah\Module\Asset\ExecutableBuilderStrategy\ExecutableBuilderStrategyInterface;
use Slothsoft\Farah\Module\Executable\ExecutableInterface;
use Slothsoft\Farah\Module\Executable\ExecutableStrategies;
use Slothsoft\Farah\Module\Executable\ResultBuilderStrategy\ResultBuilderStrategyInterface;
use Slothsoft\Farah\Module\Result\ResultStrategies;
use Slothsoft\Farah\Module\Result\StreamBuilderStrategy\StringWriterStreamBuilder;

class PrintFragmentExecutableBuilder implements ExecutableBuilderStrategyInterface, ResultBuilderStrategyInterface {
    
    public function buildExecutableStrategies(AssetInterface $context, FarahUrlArguments $args): ExecutableStrategies {
        return new ExecutableStrategies($this);
    }
    
    public function isDifferentFromDefault(FarahUrlStreamIdentifier $type): bool {
        return true;
    }
    
    public function buildResultStrategies(ExecutableInterface $context, FarahUrlStreamIdentifier $type): ResultStrategies {
        $delegate = function () use ($type): string {
            return "<print-fragment xmlns='http://schema.slothsoft.net/farah/module' type='$type'/>";
        };
        $writer = new StringWriterFromStringDelegate($delegate);
        $streamBuilder = new StringWriterStreamBuilder($writer, 'print', 'xml');
        return new ResultStrategies($streamBuilder);
    }
}