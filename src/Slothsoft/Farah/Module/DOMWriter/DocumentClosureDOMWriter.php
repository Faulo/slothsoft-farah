<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\DOMWriter;

use Slothsoft\Core\IO\Writable\DOMWriterElementFromDocumentTrait;
use Slothsoft\Core\IO\Writable\DOMWriterInterface;
use DOMDocument;

class DocumentClosureDOMWriter implements DOMWriterInterface
{
    use DOMWriterElementFromDocumentTrait;

    /**
     *
     * @var callable
     */
    private $closure;

    public function __construct(callable $closure)
    {
        $this->closure = $closure;
    }

    public function toDocument(): DOMDocument
    {
        return ($this->closure)();
    }
}

