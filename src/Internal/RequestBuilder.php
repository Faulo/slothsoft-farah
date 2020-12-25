<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Internal;

use Slothsoft\Core\Configuration\ConfigurationRequiredException;
use Slothsoft\Core\IO\Writable\Delegates\DOMWriterFromElementDelegate;
use Slothsoft\Farah\Kernel;
use Slothsoft\Farah\FarahUrl\FarahUrlArguments;
use Slothsoft\Farah\Module\Asset\AssetInterface;
use Slothsoft\Farah\Module\Asset\ExecutableBuilderStrategy\ExecutableBuilderStrategyInterface;
use Slothsoft\Farah\Module\Executable\ExecutableStrategies;
use Slothsoft\Farah\Module\Executable\ResultBuilderStrategy\DOMWriterResultBuilder;
use DOMDocument;
use DOMElement;

/**
 *
 * @author Daniel Schulz
 *        
 */
class RequestBuilder implements ExecutableBuilderStrategyInterface {

    public function buildExecutableStrategies(AssetInterface $context, FarahUrlArguments $args): ExecutableStrategies {
        $closure = function (DOMDocument $targetDoc): DOMElement {
            try {
                Kernel::getCurrentRequest();
            } catch (ConfigurationRequiredException $e) {}
            $node = $targetDoc->createElement('request');
            $node->setAttribute('lang', 'en-us'); // @TODO
            return $node;
        };
        $writer = new DOMWriterFromElementDelegate($closure);
        $resultBuilder = new DOMWriterResultBuilder($writer, 'request.xml');
        return new ExecutableStrategies($resultBuilder);
    }
}

