<?php
declare(strict_types = 1);

namespace Slothsoft\Farah\Module\Result\StreamBuilderStrategy;

use DOMDocument;
use DOMAttr;
use DOMElement;
use Masterminds\HTML5\Elements;
use Masterminds\HTML5\Serializer\OutputRules;
use Masterminds\HTML5\Serializer\Traverser;
use Slothsoft\Core\IO\Writable\ChunkWriterInterface;
use Slothsoft\Core\IO\Writable\Delegates\StringWriterFromStringDelegate;
use Slothsoft\Core\IO\Writable\DOMWriterInterface;
use Slothsoft\Core\IO\Writable\FileWriterInterface;
use Slothsoft\Core\IO\Writable\StreamWriterInterface;
use Slothsoft\Core\IO\Writable\StringWriterInterface;
use Slothsoft\Core\IO\Writable\Traits\DOMWriterElementFromDocumentTrait;
use Slothsoft\Farah\Module\Result\ResultInterface;

/**
 * Stream builder strategy that serializes a DOM writer as HTML5.
 *
 * @author Daniel Schulz
 * @since 2026-09-03
 */
final class HTML5DOMWriterStreamBuilder implements StreamBuilderStrategyInterface, DOMWriterInterface {
    use DOMWriterElementFromDocumentTrait;

    private DOMWriterInterface $writer;

    private StringWriterStreamBuilder $streamBuilder;

    public function __construct(DOMWriterInterface $writer, string $documentName = 'document') {
        $this->writer = $writer;
        $stringWriter = new StringWriterFromStringDelegate(function (): string {
            return $this->serialize($this->toDocument());
        });
        $this->streamBuilder = new StringWriterStreamBuilder($stringWriter, $documentName, 'html');
    }

    private function serialize(DOMDocument $document): string {
        $stream = fopen('php://temp', 'w+b');
        // Project names during Masterminds' traversal to avoid allocating a second DOM for large pages.
        $rules = new class($stream) extends OutputRules {

            protected function namespaceAttrs($ele): void {
                // Namespace declarations are XML syntax, not HTML attributes.
            }

            protected function attrs($ele): self {
                if (! $ele->hasAttributes()) {
                    return $this;
                }

                $attributes = [];
                foreach ($ele->attributes as $attribute) {
                    if ($attribute->namespaceURI === null or $attribute->namespaceURI === '') {
                        $attributes[] = $attribute;
                    }
                }
                foreach ($ele->attributes as $attribute) {
                    if ($attribute->namespaceURI !== null and $attribute->namespaceURI !== '') {
                        $attributes[] = $attribute;
                    }
                }

                $writtenNames = [];
                foreach ($attributes as $attribute) {
                    $name = $this->projectAttributeName($ele, $attribute);
                    if ($name === null) {
                        continue;
                    }

                    if ($this->outputMode === self::IM_IN_SVG) {
                        $name = Elements::normalizeSvgAttribute($name);
                    } elseif ($this->outputMode === self::IM_IN_MATHML) {
                        $name = Elements::normalizeMathMlAttribute($name);
                    }

                    $collisionName = $this->outputMode === self::IM_IN_HTML
                        ? strtolower($name)
                        : $name;
                    if (isset($writtenNames[$collisionName])) {
                        continue;
                    }
                    $writtenNames[$collisionName] = true;

                    $value = $this->enc($attribute->value, true);
                    $this->wr(' ')->wr($name);
                    if ($value !== '' or $attribute->namespaceURI or $this->outputMode !== self::IM_IN_HTML or $this->nonBooleanAttribute($attribute)) {
                        $this->wr('="')->wr($value)->wr('"');
                    }
                }
                return $this;
            }

            private function projectAttributeName(DOMElement $element, DOMAttr $attribute): ?string {
                $namespace = $attribute->namespaceURI ?? '';
                if ($namespace === '' or $namespace === self::NAMESPACE_HTML) {
                    return $attribute->localName;
                }
                if ($namespace === self::NAMESPACE_XMLNS or $attribute->prefix === 'xmlns') {
                    return null;
                }
                if ($namespace === self::NAMESPACE_XML) {
                    if ($element->namespaceURI === self::NAMESPACE_HTML and $attribute->localName === 'lang') {
                        return 'lang';
                    }
                    return 'xml:' . $attribute->localName;
                }
                if ($namespace === self::NAMESPACE_XLINK and ($element->namespaceURI === self::NAMESPACE_SVG or $element->namespaceURI === self::NAMESPACE_MATHML)) {
                    return 'xlink:' . $attribute->localName;
                }
                return $attribute->localName;
            }
        };
        $traverser = new class($document, $stream, $rules) extends Traverser {

            public function isLocalElement($ele): bool {
                return true;
            }
        };
        $traverser->walk();
        $rules->unsetTraverser();
        $html = stream_get_contents($stream, -1, 0);
        fclose($stream);
        return $html;
    }

    public function toDocument(): DOMDocument {
        return $this->writer->toDocument();
    }

    public function buildStringWriter(ResultInterface $context): StringWriterInterface {
        return $this->streamBuilder->buildStringWriter($context);
    }

    public function buildStreamWriter(ResultInterface $context): StreamWriterInterface {
        return $this->streamBuilder->buildStreamWriter($context);
    }

    public function buildFileWriter(ResultInterface $context): FileWriterInterface {
        return $this->streamBuilder->buildFileWriter($context);
    }

    public function buildChunkWriter(ResultInterface $context): ChunkWriterInterface {
        return $this->streamBuilder->buildChunkWriter($context);
    }

    public function buildDOMWriter(ResultInterface $context): DOMWriterInterface {
        return $this->writer;
    }

    public function buildStreamFileStatistics(ResultInterface $context): array {
        return $this->streamBuilder->buildStreamFileStatistics($context);
    }

    public function buildStreamFileName(ResultInterface $context): string {
        return $this->streamBuilder->buildStreamFileName($context);
    }

    public function buildStreamMimeType(ResultInterface $context): string {
        return $this->streamBuilder->buildStreamMimeType($context);
    }

    public function buildStreamCharset(ResultInterface $context): string {
        return $this->streamBuilder->buildStreamCharset($context);
    }

    public function buildStreamHash(ResultInterface $context): string {
        return $this->streamBuilder->buildStreamHash($context);
    }

    public function buildStreamIsBufferable(ResultInterface $context): bool {
        return $this->streamBuilder->buildStreamIsBufferable($context);
    }
}
