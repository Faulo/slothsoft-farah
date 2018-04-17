<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Results;

use Slothsoft\Core\IO\HTTPFile;
use Slothsoft\Core\IO\Writable\DOMWriterInterface;
use Slothsoft\Core\IO\Writable\FileWriterInterface;
use Slothsoft\Core\StreamWrapper\StreamWrapperInterface;
use Slothsoft\Farah\Module\FarahUrl\FarahUrl;
use Slothsoft\Farah\Module\FarahUrl\FarahUrlArguments;
use Slothsoft\Farah\Module\FarahUrl\FarahUrlResolver;
use Slothsoft\Farah\Module\FarahUrl\FarahUrlStreamIdentifier;
use DOMDocument;
use DOMElement;

/**
 *
 * @author Daniel Schulz
 *        
 */
abstract class ResultImplementation implements ResultInterface, DOMWriterInterface, FileWriterInterface
{

    private $url;

    private $defaultStreamWrapper;

    private $xmlStreamWrapper;

    public function __construct(FarahUrl $url)
    {
        $this->url = $url;
    }

    public function __toString(): string
    {
        return $this->getId();
    }

    public function getUrl(): FarahUrl
    {
        return $this->url;
    }

    public function getId(): string
    {
        return (string) $this->url;
    }

    public function getArguments(): FarahUrlArguments
    {
        return $this->url->getArguments();
    }

    public function getLinkedStylesheets(): array
    {
        return array_values(FarahUrlResolver::resolveToAsset($this->url)->lookupLinkedStylesheets());
    }

    public function getLinkedScripts(): array
    {
        return array_values(FarahUrlResolver::resolveToAsset($this->url)->lookupLinkedScripts());
    }

    public function createDefaultUrl(): FarahUrl
    {
        return $this->url->withStreamIdentifier(FarahUrlStreamIdentifier::createFromString(self::STREAM_TYPE_DEFAULT));
    }

    public function createDefaultStreamWrapper(): StreamWrapperInterface
    {
        if ($this->defaultStreamWrapper === null) {
            $this->defaultStreamWrapper = $this->loadDefaultStreamWrapper();
        }
        return $this->defaultStreamWrapper;
    }

    abstract protected function loadDefaultStreamWrapper(): StreamWrapperInterface;

    public function createXmlUrl(): FarahUrl
    {
        return $this->url->withStreamIdentifier(FarahUrlStreamIdentifier::createFromString(self::STREAM_TYPE_XML));
    }

    public function createXmlStreamWrapper(): StreamWrapperInterface
    {
        if ($this->xmlStreamWrapper === null) {
            $this->xmlStreamWrapper = $this->loadXmlStreamWrapper();
        }
        return $this->xmlStreamWrapper;
    }

    abstract protected function loadXmlStreamWrapper(): StreamWrapperInterface;

    /**
     *
     * {@inheritdoc}
     * @see \Slothsoft\Core\IO\Writable\DOMWriterInterface::toDocument()
     */
    public function toDocument(): DOMDocument
    {
        $targetDoc = new DOMDocument();
        $targetDoc->load((string) $this->createXmlUrl());
        return $targetDoc;
    }

    /**
     *
     * {@inheritdoc}
     * @see \Slothsoft\Core\IO\Writable\DOMWriterInterface::toElement()
     */
    public function toElement(DOMDocument $targetDoc): DOMElement
    {
        $fragment = $targetDoc->createDocumentFragment();
        $xml = file_get_contents((string) $this->createXmlUrl());
        $xml = preg_replace('~^\<\?xml[^?]+\?\>~', '', $xml);
        $fragment->appendXML($xml);
        foreach ($fragment->childNodes as $node) {
            if ($node->nodeType === XML_ELEMENT_NODE) {
                return $fragment->removeChild($node);
            }
        }
        throw new \DOMException("$this->url#xml does not contain an element node");
    }

    /**
     *
     * {@inheritdoc}
     * @see \Slothsoft\Core\IO\Writable\FileWriterInterface::toFile()
     */
    public function toFile(): HTTPFile
    {
        return HTTPFile::createFromPath((string) $this->createDefaultUrl());
    }

    /**
     *
     * {@inheritdoc}
     * @see \Slothsoft\Core\IO\Writable\FileWriterInterface::toString()
     */
    public function toString(): string
    {
        return file_get_contents((string) $this->createDefaultUrl());
    }
}

