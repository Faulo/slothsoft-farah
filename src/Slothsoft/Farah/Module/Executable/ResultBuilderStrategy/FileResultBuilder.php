<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Executable\ResultBuilderStrategy;

use Slothsoft\Core\IO\HTTPFile;
use Slothsoft\Farah\FarahUrl\FarahUrlStreamIdentifier;
use Slothsoft\Farah\Module\Executable\ExecutableInterface;
use Slothsoft\Farah\Module\Result\ResultStrategies;
use Slothsoft\Farah\Module\Result\StreamBuilderStrategy\FileWriterStreamBuilder;

class FileResultBuilder implements ResultBuilderStrategyInterface
{

    private $file;

    public function __construct(HTTPFile $file)
    {
        $this->file = $file;
    }

    public function buildResultStrategies(ExecutableInterface $context, FarahUrlStreamIdentifier $type): ResultStrategies
    {
        return new ResultStrategies(new FileWriterStreamBuilder($this->file));
    }
}

