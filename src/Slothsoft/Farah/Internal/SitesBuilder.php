<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Internal;

use Slothsoft\Farah\Kernel;
use Slothsoft\Farah\FarahUrl\FarahUrlArguments;
use Slothsoft\Farah\Module\Asset\AssetInterface;
use Slothsoft\Farah\Module\Asset\ExecutableBuilderStrategy\ExecutableBuilderStrategyInterface;
use Slothsoft\Farah\Module\DOMWriter\DocumentClosureDOMWriter;
use Slothsoft\Farah\Module\Executable\ExecutableStrategies;
use Slothsoft\Farah\Module\Executable\ResultBuilderStrategy\DOMWriterResultBuilder;
use DOMDocument;

/**
 *
 * @author Daniel Schulz
 *        
 */
class SitesBuilder implements ExecutableBuilderStrategyInterface
{

    public function buildExecutableStrategies(AssetInterface $context, FarahUrlArguments $args): ExecutableStrategies
    {
        $writer = new DocumentClosureDOMWriter(function (): DOMDocument {
            return Kernel::getCurrentSitemap()->lookupExecutable()
                ->lookupXmlResult()
                ->toDocument();
        });
        $resultBuilder = new DOMWriterResultBuilder($writer);
        return new ExecutableStrategies($resultBuilder);
    }
}