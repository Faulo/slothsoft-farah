<?php
namespace Slothsoft\Farah\LinkDecorator;

use DOMDocument;

/**
 *
 * @author Daniel Schulz
 *        
 */
interface LinkDecoratorInterface
{

    public function decorateDocument(DOMDocument $document, array $stylesheetAssetList, array $scriptAssetList);
}

