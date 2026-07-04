<?php
declare(strict_types = 1);

namespace Slothsoft\Farah\Module\Executable\ResultBuilderStrategy\Files;

use DOMDocument;
use Slothsoft\Core\IO\Writable\Traits\DOMWriterElementFromDocumentTrait;

/**
 * Result builder for HTML file results.
 *
 * @author Daniel Schulz
 * @since 2018-05-27
 */
final class HtmlFileResultBuilder extends AbstractFileResultBuilder {
    use DOMWriterElementFromDocumentTrait;
    
    public function toDocument(): DOMDocument {
        $doc = new DOMDocument();
        $doc->loadHTMLFile((string) $this->file);
        return $doc;
    }
}
