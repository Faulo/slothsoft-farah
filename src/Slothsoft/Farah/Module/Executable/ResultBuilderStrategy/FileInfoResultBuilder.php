<?php
namespace Slothsoft\Farah\Module\Executable\ResultBuilderStrategy;

use Slothsoft\Farah\FarahUrl\FarahUrlStreamIdentifier;
use Slothsoft\Farah\Module\Executable\Executable;
use Slothsoft\Farah\Module\Executable\ExecutableInterface;
use Slothsoft\Farah\Module\Result\ResultStrategies;
use Slothsoft\Farah\Module\Result\StreamBuilderStrategy\FileInfoStreamBuilder;
use Slothsoft\Farah\Module\Result\StreamBuilderStrategy\NullStreamBuilder;
use SplFileInfo;

class FileInfoResultBuilder implements ResultBuilderStrategyInterface
{

    private $file;

    public function __construct(SplFileInfo $file)
    {
        $this->file = $file;
    }

    public function buildResultStrategies(ExecutableInterface $context, FarahUrlStreamIdentifier $type): ResultStrategies
    {
        if ($type === Executable::resultIsXml()) {
            return new ResultStrategies(new NullStreamBuilder());
        } else {
            return new ResultStrategies(new FileInfoStreamBuilder($this->file));
        }
    }
}

