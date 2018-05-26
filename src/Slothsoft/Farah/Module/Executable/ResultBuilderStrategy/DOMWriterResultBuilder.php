<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Executable\ResultBuilderStrategy;

use Slothsoft\Core\IO\Writable\DOMWriterInterface;
use Slothsoft\Farah\FarahUrl\FarahUrlStreamIdentifier;
use Slothsoft\Farah\Module\Executable\ExecutableInterface;
use Slothsoft\Farah\Module\Result\ResultStrategies;
use Slothsoft\Farah\Module\Result\StreamBuilderStrategy\DOMWriterStreamBuilder;

class DOMWriterResultBuilder implements ResultBuilderStrategyInterface
{

    private $writer;

    public function __construct(DOMWriterInterface $writer)
    {
        $this->writer = $writer;
    }

    public function buildResultStrategies(ExecutableInterface $context, FarahUrlStreamIdentifier $type): ResultStrategies
    {
        $streamBuilder = new DOMWriterStreamBuilder($writer);
        return new ResultStrategies($streamBuilder);
    }
}

