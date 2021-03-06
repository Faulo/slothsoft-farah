<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\DOMWriter;

use Slothsoft\Core\IO\Writable\DOMWriterInterface;
use Slothsoft\Core\IO\Writable\Traits\DOMWriterElementFromDocumentTrait;
use Slothsoft\Farah\Dictionary;
use Slothsoft\Farah\FarahUrl\FarahUrl;
use DOMDocument;

class TranslationDOMWriter implements DOMWriterInterface {
    use DOMWriterElementFromDocumentTrait;

    /**
     *
     * @var DOMWriterInterface
     */
    private $source;

    /**
     *
     * @var Dictionary
     */
    private $dict;

    /**
     *
     * @var FarahUrl
     */
    private $context;

    public function __construct(DOMWriterInterface $source, Dictionary $dict, FarahUrl $context) {
        $this->source = $source;
        $this->dict = $dict;
        $this->context = $context;
    }

    public function toDocument(): DOMDocument {
        $document = $this->source->toDocument();
        $this->dict->translateDoc($document, $this->context);

        return $document;
    }
}

