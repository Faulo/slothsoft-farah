<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Executable\ResultBuilderStrategy;

use Slothsoft\Core\IO\Writable\FileWriterInterface;
use Slothsoft\Farah\FarahUrl\FarahUrlStreamIdentifier;
use Slothsoft\Farah\Module\Executable\ExecutableInterface;
use Slothsoft\Farah\Module\Result\ResultStrategies;
use Slothsoft\Farah\Module\Result\StreamBuilderStrategy\FileWriterStreamBuilder;
use Slothsoft\Farah\Module\Executable\Executable;
use Slothsoft\Farah\Module\Result\StreamBuilderStrategy\NullStreamBuilder;

class FileWriterResultBuilder implements ResultBuilderStrategyInterface
{

    private $writer;

    public function __construct(FileWriterInterface $writer)
    {
        $this->writer = $writer;
    }

    public function buildResultStrategies(ExecutableInterface $context, FarahUrlStreamIdentifier $type): ResultStrategies
    {
        if ($type === Executable::resultIsXml()) {
            return new ResultStrategies(new NullStreamBuilder());
        } else {
            return new ResultStrategies(new FileWriterStreamBuilder($this->writer));
        }
    }
}
