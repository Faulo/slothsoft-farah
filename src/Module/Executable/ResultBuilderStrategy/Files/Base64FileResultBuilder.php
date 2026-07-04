<?php
declare(strict_types = 1);

namespace Slothsoft\Farah\Module\Executable\ResultBuilderStrategy\Files;

use DOMDocument;
use DOMElement;
use Slothsoft\Core\IO\Writable\Traits\DOMWriterDocumentFromElementTrait;

/**
 * Result builder for Base64-encoded file results.
 *
 * @author Daniel Schulz
 * @since 2018-05-27
 */
final class Base64FileResultBuilder extends AbstractFileResultBuilder {
    use DOMWriterDocumentFromElementTrait;
    
    public function toElement(DOMDocument $targetDoc): DOMElement {
        $element = $targetDoc->createElement('base64');
        $element->textContent = base64_encode(file_get_contents((string) $this->file));
        return $element;
    }
}

