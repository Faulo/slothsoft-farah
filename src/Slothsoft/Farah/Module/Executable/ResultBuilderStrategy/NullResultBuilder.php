<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Executable\ResultBuilderStrategy;

use Slothsoft\Core\IO\AdapterDelegates\DOMWriterFromElementDelegate;
use Slothsoft\Farah\FarahUrl\FarahUrlStreamIdentifier;
use Slothsoft\Farah\Module\Executable\Executable;
use Slothsoft\Farah\Module\Executable\ExecutableInterface;
use Slothsoft\Farah\Module\Result\ResultStrategies;
use Slothsoft\Farah\Module\Result\StreamBuilderStrategy\DOMWriterStreamBuilder;
use Slothsoft\Farah\Module\Result\StreamBuilderStrategy\NullStreamBuilder;
use DOMDocument;
use DOMElement;

class NullResultBuilder implements ResultBuilderStrategyInterface
{

    public function buildResultStrategies(ExecutableInterface $context, FarahUrlStreamIdentifier $type): ResultStrategies
    {
        if ($type === Executable::resultIsXml()) {
            $writer = new DOMWriterFromElementDelegate(function (DOMDocument $targetDoc): DOMElement {
                return $targetDoc->createElement('null');
            });
            return new ResultStrategies(new DOMWriterStreamBuilder($writer));
        } else {
            return new ResultStrategies(new NullStreamBuilder());
        }
    }
}

