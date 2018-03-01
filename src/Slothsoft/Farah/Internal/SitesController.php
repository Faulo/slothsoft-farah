<?php
namespace Slothsoft\Farah\Internal;

use Slothsoft\Core\XML\LeanElement;
use Slothsoft\Farah\HTTPClosure;
use Slothsoft\Farah\Kernel;
use Slothsoft\Farah\Module\Module;
use Slothsoft\Farah\Module\Controllers\ControllerImplementation;
use Slothsoft\Farah\Module\FarahUrl\FarahUrl;
use Slothsoft\Farah\Module\PathResolvers\PathResolverCatalog;
use Slothsoft\Farah\Module\PathResolvers\PathResolverInterface;
use Slothsoft\Farah\Module\Results\DOMDocumentResult;
use Slothsoft\Farah\Module\Results\ResultInterface;

/**
 *
 * @author Daniel Schulz
 *        
 */
class SitesController extends ControllerImplementation
{
    public function createResult(FarahUrl $url) : ResultInterface {
        return new DOMDocumentResult($url, Kernel::getInstance()->getSitesDocument());
    }
    public function createPathResolver() : PathResolverInterface {
        $definition = $this->getAsset();
        $element = LeanElement::createOneFromArray(Module::TAG_CLOSURE, []);
        $closure = $definition->addChildElement($element);
        $closure->setClosure(new HTTPClosure([], function() { return 'hallo welt'; }));
        return PathResolverCatalog::createMapPathResolver(
            $definition,
            [
                '/' => $definition,
                '/test' => $closure
            ]
        );
    }
}

