<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Executable\ResultBuilderStrategy\Files;

use Slothsoft\Core\IO\Writable\DOMWriterInterface;
use Slothsoft\Core\IO\Writable\FileWriterInterface;
use Slothsoft\Core\IO\Writable\Decorators\DOMWriterMemoryCache;
use Slothsoft\Farah\FarahUrl\FarahUrlStreamIdentifier;
use Slothsoft\Farah\Module\Executable\Executable;
use Slothsoft\Farah\Module\Executable\ExecutableInterface;
use Slothsoft\Farah\Module\Executable\ResultBuilderStrategy\ResultBuilderStrategyInterface;
use Slothsoft\Farah\Module\Result\ResultStrategies;
use Slothsoft\Farah\Module\Result\StreamBuilderStrategy\DOMWriterStreamBuilder;
use Slothsoft\Farah\Module\Result\StreamBuilderStrategy\FileInfoStreamBuilder;
use SplFileInfo;

abstract class AbstractFileResultBuilder implements ResultBuilderStrategyInterface, FileWriterInterface, DOMWriterInterface
{
    protected $file;

    public function __construct(SplFileInfo $file)
    {
        $this->file = $file;
    }

    public function buildResultStrategies(ExecutableInterface $context, FarahUrlStreamIdentifier $type): ResultStrategies
    {
        if ($type === Executable::resultIsXml()) {
            $streamBuilder = new DOMWriterStreamBuilder(new DOMWriterMemoryCache($this), $this->file->getFilename());
        } else {
            $streamBuilder = new FileInfoStreamBuilder($this->file, $this->file->getFilename());
        }
        return new ResultStrategies($streamBuilder);
    }
    
    public function toFile() : SplFileInfo {
        return $this->file;
    }
}