<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Internal;

use Slothsoft\Core\DOMHelper;
use Slothsoft\Core\Configuration\ConfigurationRequiredException;
use Slothsoft\Core\IO\Writable\Delegates\DOMWriterFromDocumentDelegate;
use Slothsoft\Farah\Kernel;
use Slothsoft\Farah\FarahUrl\FarahUrlArguments;
use Slothsoft\Farah\Module\Asset\AssetInterface;
use Slothsoft\Farah\Module\Asset\ExecutableBuilderStrategy\ExecutableBuilderStrategyInterface;
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
        $writer = new DOMWriterFromDocumentDelegate(function (): DOMDocument {
            try {
                return Kernel::getCurrentSitemap()->lookupExecutable()
                    ->lookupXmlResult()
                    ->lookupDOMWriter()
                    ->toDocument();
            } catch (ConfigurationRequiredException $e) {
                $doc = new DOMDocument();
                $node = $doc->createElementNS(DOMHelper::NS_FARAH_SITES, 'domain');
                $node->textContent = $e->getMessage();
                $doc->appendChild($node);
                return $doc;
            }
        });
        $resultBuilder = new DOMWriterResultBuilder($writer, 'sites.xml');
        return new ExecutableStrategies($resultBuilder);
    }
}