<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Exception;

use Slothsoft\Core\DOMHelper;
use Slothsoft\Core\IO\Writable\DOMWriterInterface;
use Slothsoft\Farah\Module\Module;
use DOMDocument;
use DOMElement;
use ErrorException;
use Throwable;

/**
 *
 * @author Daniel Schulz
 *        
 */
class ExceptionContext implements DOMWriterInterface
{

    public static function append(Throwable $exception, array $data = []): Throwable
    {
        if (isset($exception->exceptionContext)) {
            $exception->exceptionContext->addData($data);
        } else {
            $exception->exceptionContext = new ExceptionContext($exception, $data);
        }
        return $exception;
    }

    private $ownerException;

    private $data;

    private function __construct(Throwable $ownerException, array $data)
    {
        $this->ownerException = $ownerException;
        $this->data = $data;
    }

    public function addData(array $data)
    {
        $this->data += $data;
    }

    public function getModule()
    {
        return $this->data['module'] ?? null;
    }

    public function getAsset()
    {
        return $this->data['asset'] ?? null;
    }

    public function getResult()
    {
        return $this->data['result'] ?? null;
    }

    public function getClass()
    {
        return $this->data['class'] ?? null;
    }

    public function toElement(DOMDocument $targetDoc): DOMElement
    {
        $name = get_class($this->ownerException);
        
        if ($this->ownerException instanceof ErrorException) {
            $code = $this->ownerException->getSeverity();
            $constList = array_flip(array_slice(get_defined_constants(true)['Core'], 0, 15, true));
            if (isset($constList[$code])) {
                $name .= "[$constList[$code]]";
            }
        }
        
        $element = $targetDoc->createElementNS(DOMHelper::NS_FARAH_MODULE, Module::TAG_ERROR);
        $element->setAttribute('name', $name);
        $element->setAttribute('code', (string) $this->ownerException->getCode());
        $element->setAttribute('file', $this->ownerException->getFile());
        $element->setAttribute('line', (string) $this->ownerException->getLine());
        $element->setAttribute('message', $this->ownerException->getMessage());
        $element->setAttribute('trace', $this->ownerException->getTraceAsString());
        
        if ($module = $this->getModule()) {
            $element->setAttribute('module', $module->getId());
        }
        if ($asset = $this->getAsset()) {
            $element->setAttribute('asset', $asset->getId());
        }
        if ($result = $this->getResult()) {
            $element->setAttribute('result', $result->getId());
        }
        if ($class = $this->getClass()) {
            $element->setAttribute('class', $class);
        }
        
        if ($exception = $this->ownerException->getPrevious()) {
            $element->appendChild(self::append($exception)->exceptionContext->toElement($targetDoc));
        }
        
        return $element;
    }

    public function toDocument(): DOMDocument
    {
        $targetDoc = new DOMDocument();
        try {
            // let's retrieve the error template to make sure it exists
            assert((bool) @file_get_contents('farah://slothsoft@farah/xsl/error'));
            $targetDoc->appendChild($targetDoc->createProcessingInstruction('xml-stylesheet', 'type="text/xsl" href="/slothsoft@farah/xsl/error"'));
        } catch (Throwable $e) {
            // we don't really care if it doesn't tho
        }
        $targetDoc->appendChild($this->toElement($targetDoc));
        return $targetDoc;
    }
}

