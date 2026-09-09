<?php
declare(strict_types = 1);

namespace Slothsoft\Farah\Module\Executable\ResultBuilderStrategy\Files;

use DOMDocument;
use Slothsoft\Core\DOMHelper;
use Slothsoft\Core\IO\Writable\Traits\DOMWriterElementFromDocumentTrait;
use Slothsoft\Farah\FarahUrl\FarahUrl;
use Slothsoft\Farah\FarahUrl\FarahUrlStreamIdentifier;
use Slothsoft\Farah\Module\DOMWriter\EmbedIncludesDOMWriter;
use Slothsoft\Farah\Module\Executable\Executable;
use Slothsoft\Farah\Module\Executable\ExecutableInterface;
use Slothsoft\Farah\Module\Manifest\Manifest;
use Slothsoft\Farah\Module\Result\ResultStrategies;
use Slothsoft\Farah\Module\Result\StreamBuilderStrategy\DOMWriterStreamBuilder;
use Slothsoft\Farah\Module\Result\StreamBuilderStrategy\FileInfoStreamBuilder;
use Slothsoft\Farah\Module\Result\StreamBuilderStrategy\HTML5DOMWriterStreamBuilder;
use SplFileInfo;

/**
 * Result builder for XML file results.
 *
 * @author Daniel Schulz
 * @since 2018-05-27
 */
final class XmlFileResultBuilder extends AbstractFileResultBuilder {
    use DOMWriterElementFromDocumentTrait;

    private const MIME_XHTML = 'application/xhtml+xml';

    private string $mimeType;

    public function __construct(FarahUrl $url, SplFileInfo $file, string $mimeType = 'application/xml') {
        parent::__construct($url, $file);
        $this->mimeType = $mimeType;
    }
    
    public function toDocument(): DOMDocument {
        $document = DOMHelper::loadDocument((string) $this->file);
        $document->documentURI = (string) $this->url;
        return $document;
    }
    
    public function isDifferentFromDefault(FarahUrlStreamIdentifier $type): bool {
        return ($this->mimeType === self::MIME_XHTML and $type === Executable::resultIsHtml())
            or $this->url->getArguments()->get(Manifest::PARAM_INCLUDES) === Manifest::PARAM_INCLUDES_EMBED;
    }
    
    public function buildResultStrategies(ExecutableInterface $context, FarahUrlStreamIdentifier $type): ResultStrategies {
        $embedIncludes = $this->url->getArguments()->get(Manifest::PARAM_INCLUDES) === Manifest::PARAM_INCLUDES_EMBED;
        $writer = $embedIncludes ? new EmbedIncludesDOMWriter($this, $this->url) : $this;

        if ($this->mimeType === self::MIME_XHTML and $type === Executable::resultIsHtml()) {
            $streamBuilder = new HTML5DOMWriterStreamBuilder($writer, $this->file->getFilename());
        } elseif ($embedIncludes) {
            $streamBuilder = new DOMWriterStreamBuilder($writer, $this->file->getFilename());
        } else {
            $streamBuilder = new FileInfoStreamBuilder($this->file, $this->file->getFilename());
        }
        
        return new ResultStrategies($streamBuilder);
    }
}
