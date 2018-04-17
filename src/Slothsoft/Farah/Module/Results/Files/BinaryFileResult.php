<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Results\Files;

use Slothsoft\Core\StreamWrapper\StreamWrapperInterface;
use Slothsoft\Farah\StreamWrapper\DocumentStreamWrapper;
use DOMDocument;

/**
 *
 * @author Daniel Schulz
 *        
 */
class BinaryFileResult extends FileResult
{

    /**
     *
     * {@inheritdoc}
     * @see \Slothsoft\Farah\Module\Results\ResultImplementation::loadXmlStreamWrapper()
     */
    protected function loadXmlStreamWrapper(): StreamWrapperInterface
    {
        $dataDoc = new DOMDocument();
        $element = $dataDoc->createElement(basename(get_class($this)));
        $element->textContent = base64_encode($this->file->getContents());
        $dataDoc->appendChild($element);
        return new DocumentStreamWrapper($dataDoc);
    }
}