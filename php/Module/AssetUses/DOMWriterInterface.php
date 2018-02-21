<?php declare(strict_types=1);
namespace Slothsoft\Farah\Module\AssetUses;

use DOMDocument;
use DOMElement;

/**
 *
 * @author Daniel Schulz
 *        
 */
interface DOMWriterInterface
{

    public function toElement(DOMDocument $targetDoc): DOMElement;

    public function toDocument(): DOMDocument;
}

