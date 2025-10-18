<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Executable\ResultBuilderStrategy\Files;

use Slothsoft\Core\DOMHelper;
use Slothsoft\Core\IO\Writable\Traits\DOMWriterElementFromDocumentTrait;
use Slothsoft\Farah\FarahUrl\FarahUrlStreamIdentifier;
use Slothsoft\Farah\Module\Executable\ExecutableInterface;
use Slothsoft\Farah\Module\Result\ResultStrategies;
use Slothsoft\Farah\Module\Result\StreamBuilderStrategy\FileInfoStreamBuilder;
use DOMDocument;

class XmlFileResultBuilder extends AbstractFileResultBuilder {
    use DOMWriterElementFromDocumentTrait;
    
    public function toDocument(): DOMDocument {
        $document = DOMHelper::loadDocument((string) $this->file);
        $document->documentURI = (string) $this->url;
        return $document;
    }
    
    public function isDifferentFromDefault(FarahUrlStreamIdentifier $type): bool {
        return false;
    }
    
    public function buildResultStrategies(ExecutableInterface $context, FarahUrlStreamIdentifier $type): ResultStrategies {
        $streamBuilder = new FileInfoStreamBuilder($this->file, $this->file->getFilename());
        return new ResultStrategies($streamBuilder);
    }
}
