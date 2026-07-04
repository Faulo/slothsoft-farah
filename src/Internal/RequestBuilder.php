<?php
declare(strict_types = 1);

namespace Slothsoft\Farah\Internal;

use DOMDocument;
use DOMElement;
use Slothsoft\Core\Configuration\ConfigurationRequiredException;
use Slothsoft\Core\DOMHelper;
use Slothsoft\Core\IO\Writable\Delegates\DOMWriterFromElementDelegate;
use Slothsoft\Farah\FarahUrl\FarahUrlArguments;
use Slothsoft\Farah\Kernel;
use Slothsoft\Farah\Module\Asset\AssetInterface;
use Slothsoft\Farah\Module\Asset\ExecutableBuilderStrategy\ExecutableBuilderStrategyInterface;
use Slothsoft\Farah\Module\Executable\ExecutableStrategies;
use Slothsoft\Farah\Module\Executable\ResultBuilderStrategy\DOMWriterResultBuilder;
use Slothsoft\Farah\Module\Manifest\Manifest;

/**
 * Executable builder for exposing the current Farah request as XML.
 *
 * @author Daniel Schulz
 * @since 2018-05-27
 */
final class RequestBuilder implements ExecutableBuilderStrategyInterface {
    
    public function buildExecutableStrategies(AssetInterface $context, FarahUrlArguments $args): ExecutableStrategies {
        $closure = function (DOMDocument $targetDoc) use ($args): DOMElement {
            $rootNode = $targetDoc->createElementNS(DOMHelper::NS_FARAH_MODULE, Manifest::TAG_REQUEST_INFO);
            $rootNode->setAttribute(Manifest::ATTR_SCHEMAVERSION, '1.1');
            try {
                $request = Kernel::getCurrentRequest();
                $rootNode->setAttribute(Manifest::ATTR_HREF, (string) $request->getUri());
            } catch (ConfigurationRequiredException) {
            }
            
            try {
                $pageUrl = Kernel::getCurrentPage();
                $rootNode->setAttribute(Manifest::ATTR_URL, (string) $pageUrl);
            } catch (ConfigurationRequiredException) {
            }
            
            foreach ($args as $name => $value) {
                if (is_string($value)) {
                    $childNode = $targetDoc->createElementNS(DOMHelper::NS_FARAH_MODULE, 'param');
                    $childNode->setAttribute(Manifest::ATTR_PARAM_KEY, $name);
                    $childNode->setAttribute(Manifest::ATTR_PARAM_VAL, $value);
                    $rootNode->appendChild($childNode);
                }
            }
            
            return $rootNode;
        };
        $writer = new DOMWriterFromElementDelegate($closure);
        $resultBuilder = new DOMWriterResultBuilder($writer, 'request');
        return new ExecutableStrategies($resultBuilder);
    }
}

