<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Executable\ResultBuilderStrategy;

use Slothsoft\Core\IO\Writable\FileWriterInterface;
use Slothsoft\Farah\FarahUrl\FarahUrlStreamIdentifier;
use Slothsoft\Farah\Module\Executable\ExecutableInterface;
use Slothsoft\Farah\Module\Result\ResultStrategies;
use Slothsoft\Farah\Module\Result\StreamBuilderStrategy\FileWriterStreamBuilder;

class FileWriterResultBuilder implements ResultBuilderStrategyInterface
{

    private $writer;

    private $fileName;

    public function __construct(FileWriterInterface $writer, string $fileName)
    {
        $this->writer = $writer;
        $this->fileName = $fileName;
    }

    public function buildResultStrategies(ExecutableInterface $context, FarahUrlStreamIdentifier $type): ResultStrategies
    {
        $streamBuilder = new FileWriterStreamBuilder($this->writer, $this->fileName);
        return new ResultStrategies($streamBuilder);
    }
}

