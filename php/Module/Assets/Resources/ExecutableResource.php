<?php declare(strict_types=1);
namespace Slothsoft\Farah\Module\Assets\Resources;

use Slothsoft\Core\DOMHelper;
use Slothsoft\Farah\HTTPFile;
use Slothsoft\Farah\Module\FarahUrl;
use Slothsoft\Farah\Module\AssetDefinitions\AssetDefinitionInterface;
use Slothsoft\Farah\Module\AssetDefinitions\ClosurableInterface;
use Slothsoft\Farah\Module\AssetUses\DOMWriterInterface;
use Slothsoft\Farah\Module\AssetUses\FileWriterInterface;
use Slothsoft\Farah\Module\Assets\Resource;
use DOMDocument;
use DOMDocumentFragment;
use DOMElement;
use DOMNode;
use InvalidArgumentException;

/**
 *
 * @author Daniel Schulz
 *        
 */
class ExecutableResource extends Resource implements DOMWriterInterface, FileWriterInterface
{

    private $hasRun = false;

    private $result;

    public function init(AssetDefinitionInterface $definition, FarahUrl $url)
    {
        parent::init($definition, $url);
        
        assert($definition instanceof ClosurableInterface, "ExecutableResource requires an AssetDefinition that implements ClosurableInterface.");
    }

    public function toElement(DOMDocument $targetDoc): DOMElement
    {
        $this->runClosure();
        
        $ret = null;
        switch (true) {
            case $this->result instanceof DOMWriterInterface:
                $ret = $this->result->toElement($targetDoc);
                break;
            case $this->result instanceof FileWriterInterface:
                $ret = $targetDoc->importNode($this->result->toFile()->getDocument()->documentElement, true);
                break;
            case $this->result instanceof DOMDocument:
                $ret = $targetDoc->importNode($this->result->documentElement, true);
                break;
            case $this->result instanceof DOMElement:
                $ret = $targetDoc->importNode($this->result, true);
                break;
            case $this->result instanceof DOMDocumentFragment:
                $ret = $targetDoc->importNode($this->result->firstChild, true);
                break;
            case $this->result instanceof HTTPFile:
                $tmpDoc = $this->result->getDocument();
                $ret = $targetDoc->importNode($tmpDoc->documentElement, true);
                break;
            case is_object($this->result):
                throw new InvalidArgumentException("Closure return type ".get_class($this->result)." is not supported by this implementation.");
            default:
                $definition = $this->getDefinition();
                $ret = $targetDoc->createElementNS(DOMHelper::NS_FARAH_MODULE, $definition->getTag());
                $ret->appendChild($targetDoc->createTextNode($this->result));
                break;
        }
        return $ret;
    }

    public function toDocument(): DOMDocument
    {
        $this->runClosure();
        
        $ret = null;
        switch (true) {
            case $this->result instanceof DOMWriterInterface:
                $ret = $this->result->toDocument();
                break;
            case $this->result instanceof FileWriterInterface:
                $ret = $this->result->toFile()->getDocument();
                break;
            case $this->result instanceof DOMDocument:
                $ret = $this->result;
                break;
            case $this->result instanceof DOMNode:
                $ret = new DOMDocument();
                $ret->appendChild($ret->importNode($this->result, true));
                break;
            case $this->result instanceof HTTPFile:
                $ret = $this->result->getDocument();
                break;
            case is_object($this->result):
                throw new InvalidArgumentException("Closure return type ".get_class($this->result)." is not supported by this implementation.");
            default:
                $definition = $this->getDefinition();
                $ret = new DOMDocument();
                $node = $ret->createElementNS(DOMHelper::NS_FARAH_MODULE, $definition->getTag());
                $node->appendChild($ret->createTextNode($this->result));
                $ret->appendChild($node);
                break;
        }
        return $ret;
    }

    public function toFile(): HTTPFile
    {
        $this->runClosure();
        
        $ret = null;
        switch (true) {
            case $this->result instanceof DOMWriterInterface:
                $ret = HTTPFile::createFromDocument($this->result->toDocument());
                break;
            case $this->result instanceof FileWriterInterface:
                $ret = $this->result->toFile();
                break;
            case $this->result instanceof DOMDocument:
                $ret = HTTPFile::createFromDocument($this->result);
                break;
            case is_object($this->result):
                throw new InvalidArgumentException("Closure return type ".get_class($this->result)." is not supported by this implementation.");
            default:
                $ret = HTTPFile::createFromString($this->result, $this->getPath() . '.txt');
                break;
        }
        return $ret;
    }

    public function toString(): string
    {
        $this->runClosure();
        
        $ret = null;
        switch (true) {
            case $this->result instanceof DOMDocument:
                $ret = $this->result->saveXML();
                break;
            case is_object($this->result):
                throw new InvalidArgumentException(
                    sprintf('Closure return type "%s" is not supported by this implementation.', get_class($this->result))
                );
            default:
                $ret = $this->result;
                break;
        }
        return $ret;
    }

    private function runClosure()
    {
        if ($this->hasRun === false) {
            $this->hasRun = true;
            $this->result = $this->getDefinition()
                ->getClosure()
                ->run($this);
        }
    }
}

