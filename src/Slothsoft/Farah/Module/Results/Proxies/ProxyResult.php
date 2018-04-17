<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Results\Proxies;

use Slothsoft\Core\IO\HTTPFile;
use Slothsoft\Core\StreamWrapper\StreamWrapperInterface;
use Slothsoft\Farah\Module\Results\ResultImplementation;
use Slothsoft\Farah\Module\Results\ResultInterface;
use DOMDocument;
use DOMElement;

/**
 *
 * @author Daniel Schulz
 *        
 */
abstract class ProxyResult extends ResultImplementation
{

    private function getResult(): ResultInterface
    {
        if ($this->result === null) {
            $this->result = $this->loadProxiedResult();
        }
        return $this->result;
    }

    abstract protected function loadProxiedResult(): ResultInterface;

    /**
     *
     * {@inheritdoc}
     * @see \Slothsoft\Farah\Module\Results\ResultImplementation::loadDefaultStreamWrapper()
     */
    protected function loadDefaultStreamWrapper(): StreamWrapperInterface
    {
        return $this->getResult()->createDefaultStreamWrapper();
    }

    /**
     *
     * {@inheritdoc}
     * @see \Slothsoft\Farah\Module\Results\ResultImplementation::loadXmlStreamWrapper()
     */
    protected function loadXmlStreamWrapper(): StreamWrapperInterface
    {
        return $this->getResult()->createXmlStreamWrapper();
    }

    /**
     *
     * {@inheritdoc}
     * @see \Slothsoft\Farah\Module\Results\ResultInterface::exists()
     */
    public function exists(): bool
    {
        return $this->getResult()->exists();
    }

    /**
     *
     * {@inheritdoc}
     * @see \Slothsoft\Farah\Module\Results\ResultImplementation::toDocument()
     */
    public function toDocument(): DOMDocument
    {
        return $this->getResult()->toDocument();
    }

    /**
     *
     * {@inheritdoc}
     * @see \Slothsoft\Farah\Module\Results\ResultImplementation::toElement()
     */
    public function toElement(DOMDocument $targetDoc): DOMElement
    {
        return $this->getResult()->toElement($targetDoc);
    }

    /**
     *
     * {@inheritdoc}
     * @see \Slothsoft\Farah\Module\Results\ResultImplementation::toFile()
     */
    public function toFile(): HTTPFile
    {
        return $this->getResult()->toFile();
    }

    /**
     *
     * {@inheritdoc}
     * @see \Slothsoft\Farah\Module\Results\ResultImplementation::toString()
     */
    public function toString(): string
    {
        return $this->getResult()->toString();
    }
}

