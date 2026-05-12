<?php
declare(strict_types = 1);

namespace Slothsoft\Farah\Module\Executable\ResultBuilderStrategy\Files;

use DOMDocument;
use Slothsoft\Core\DOMHelper;
use Slothsoft\Core\IO\Writable\Traits\DOMWriterElementFromDocumentTrait;
use Slothsoft\Farah\FarahUrl\FarahUrlStreamIdentifier;
use Slothsoft\Farah\Module\DOMWriter\EmbedIncludesDOMWriter;
use Slothsoft\Farah\Module\Executable\ExecutableInterface;
use Slothsoft\Farah\Module\Manifest\Manifest;
use Slothsoft\Farah\Module\Result\ResultStrategies;
use Slothsoft\Farah\Module\Result\StreamBuilderStrategy\DOMWriterStreamBuilder;
use Slothsoft\Farah\Module\Result\StreamBuilderStrategy\FileInfoStreamBuilder;

class XmlFileResultBuilder extends AbstractFileResultBuilder {
    use DOMWriterElementFromDocumentTrait;
    
    public function toDocument(): DOMDocument {
        $document = DOMHelper::loadDocument((string) $this->file);
        $document->documentURI = (string) $this->url;
        return $document;
    }
    
    public function isDifferentFromDefault(FarahUrlStreamIdentifier $type): bool {
        return $this->url->getArguments()->get(Manifest::PARAM_INCLUDES) === Manifest::PARAM_INCLUDES_EMBED;
    }
    
    public function buildResultStrategies(ExecutableInterface $context, FarahUrlStreamIdentifier $type): ResultStrategies {
        $streamBuilder = match ($this->url->getArguments()->get(Manifest::PARAM_INCLUDES)) {
            Manifest::PARAM_INCLUDES_EMBED => new DOMWriterStreamBuilder(new EmbedIncludesDOMWriter($this, $this->url), $this->file->getFilename()),
            default => new FileInfoStreamBuilder($this->file, $this->file->getFilename()),
        };
        
        return new ResultStrategies($streamBuilder);
    }
}
