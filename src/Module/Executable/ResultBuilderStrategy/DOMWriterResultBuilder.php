<?php
declare(strict_types = 1);

namespace Slothsoft\Farah\Module\Executable\ResultBuilderStrategy;

use Slothsoft\Core\IO\Writable\DOMWriterInterface;
use Slothsoft\Farah\FarahUrl\FarahUrlStreamIdentifier;
use Slothsoft\Farah\Module\Executable\Executable;
use Slothsoft\Farah\Module\Executable\ExecutableInterface;
use Slothsoft\Farah\Module\Result\ResultStrategies;
use Slothsoft\Farah\Module\Result\StreamBuilderStrategy\DOMWriterStreamBuilder;
use Slothsoft\Farah\Module\Result\StreamBuilderStrategy\HTML5DOMWriterStreamBuilder;

/**
 * Result builder strategy for d o m writer executable results.
 *
 * @author Daniel Schulz
 * @since 2018-05-27
 */
final class DOMWriterResultBuilder implements ResultBuilderStrategyInterface {
    
    private DOMWriterInterface $writer;
    
    private string $fileName;
    
    public function __construct(DOMWriterInterface $writer, string $fileName = 'index') {
        $this->writer = $writer;
        $this->fileName = $fileName;
    }
    
    public function isDifferentFromDefault(FarahUrlStreamIdentifier $type): bool {
        return $type === Executable::resultIsHtml();
    }
    
    public function buildResultStrategies(ExecutableInterface $context, FarahUrlStreamIdentifier $type): ResultStrategies {
        $streamBuilder = $type === Executable::resultIsHtml()
            ? new HTML5DOMWriterStreamBuilder($this->writer, $this->fileName)
            : new DOMWriterStreamBuilder($this->writer, $this->fileName);
        return new ResultStrategies($streamBuilder);
    }
}
