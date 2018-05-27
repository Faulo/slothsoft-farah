<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Executable\ResultBuilderStrategy\Files;

use Slothsoft\Core\IO\HTTPFile;
use Slothsoft\Core\IO\Writable\DOMWriterInterface;
use Slothsoft\Farah\FarahUrl\FarahUrlStreamIdentifier;
use Slothsoft\Farah\Module\Executable\Executable;
use Slothsoft\Farah\Module\Executable\ExecutableInterface;
use Slothsoft\Farah\Module\Executable\ResultBuilderStrategy\ResultBuilderStrategyInterface;
use Slothsoft\Farah\Module\Result\ResultStrategies;
use Slothsoft\Farah\Module\Result\StreamBuilderStrategy\DOMWriterStreamBuilder;
use Slothsoft\Farah\Module\Result\StreamBuilderStrategy\FileWriterStreamBuilder;

abstract class AbstractFileResultBuilder implements ResultBuilderStrategyInterface, DOMWriterInterface
{

    protected $file;

    public function __construct(HTTPFile $file)
    {
        $this->file = $file;
    }

    public function buildResultStrategies(ExecutableInterface $context, FarahUrlStreamIdentifier $type): ResultStrategies
    {
        if ($type === Executable::resultIsXml()) {
            $streamBuilder = new DOMWriterStreamBuilder($this);
        } else {
            $streamBuilder = new FileWriterStreamBuilder($this->file);
        }
        return new ResultStrategies($streamBuilder);
    }
}