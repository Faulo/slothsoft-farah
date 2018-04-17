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
class HtmlFileResult extends FileResult
{
    /**
     * {@inheritDoc}
     * @see \Slothsoft\Farah\Module\Results\ResultImplementation::loadXmlStreamWrapper()
     */
    protected function loadXmlStreamWrapper() : StreamWrapperInterface
    {
        $dataDoc = new DOMDocument();
        $dataDoc->loadHTMLFile($this->file->getPath());
        return new DocumentStreamWrapper($dataDoc);
    }
}

