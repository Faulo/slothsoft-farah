<?php
declare(strict_types = 1);

namespace Slothsoft\Farah\Module\Executable\ResultBuilderStrategy\Files;

use DOMDocument;
use DOMElement;
use Slothsoft\Core\IO\FileInfo;
use Slothsoft\Core\IO\Writable\Traits\DOMWriterDocumentFromElementTrait;

/**
 * Result builder for plain-text file results.
 *
 * @author Daniel Schulz
 * @since 2018-05-27
 */
final class TextFileResultBuilder extends AbstractFileResultBuilder {
    use DOMWriterDocumentFromElementTrait;
    
    public function toElement(DOMDocument $targetDoc): DOMElement {
        $contents = FileInfo::readContents($this->file);
        $element = $targetDoc->createElement('text');
        $element->appendChild($targetDoc->createCDATASection($contents));
        return $element;
    }
}
