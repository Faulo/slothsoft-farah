<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\LinkDecorator;

use Slothsoft\Farah\Module\Asset\AssetInterface;
use DOMDocument;

/**
 *
 * @author Daniel Schulz
 *        
 */
interface LinkDecoratorInterface
{

    public function setNamespace(string $ns);

    public function setTarget(DOMDocument $document);

    public function linkStylesheets(AssetInterface ...$stylesheets);

    public function linkScripts(AssetInterface ...$scripts);
}

