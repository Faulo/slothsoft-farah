<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\DOMWriter;

use Ds\Set;
use Slothsoft\Core\DOMHelper;
use Slothsoft\Core\IO\Writable\DOMWriterInterface;
use Slothsoft\Core\IO\Writable\Traits\DOMWriterElementFromDocumentTrait;
use Slothsoft\Farah\FarahUrl\FarahUrl;
use Slothsoft\Farah\Module\Module;
use DOMDocument;
use DOMElement;

class EmbedIncludesDOMWriter implements DOMWriterInterface {
    use DOMWriterElementFromDocumentTrait;
    
    private DOMWriterInterface $source;
    
    private FarahUrl $url;
    
    private ?DOMDocument $document = null;
    
    public function __construct(DOMWriterInterface $source, FarahUrl $url) {
        $this->source = $source;
        $this->url = $url;
    }
    
    public function toDocument(): DOMDocument {
        if ($this->document === null) {
            $this->document = $this->source->toDocument();
            self::embedIncludes($this->document, $this->url);
        }
        
        return $this->document;
    }
    
    public static function embedIncludes(DOMDocument $document, FarahUrl $base): void {
        $urls = new Set();
        $urls->add($base);
        
        $xpath = DOMHelper::loadXPath($document, DOMHelper::XPATH_W3C);
        
        do {
            $includeNodes = [
                ...$xpath->evaluate('/*/xsl:include[@href]')
            ];
            
            /** @var $includeNode DOMElement */
            foreach ($includeNodes as $includeNode) {
                $href = $includeNode->getAttribute('href');
                $url = FarahUrl::createFromReference($href);
                
                if (! $urls->contains($url)) {
                    $urls->add($url);
                    
                    $includedNode = Module::resolveToDOMWriter($url)->toElement($document);
                    while ($node = $includedNode->firstChild) {
                        $includeNode->parentNode->insertBefore($node, $includeNode);
                    }
                }
                $includeNode->parentNode->removeChild($includeNode);
            }
        } while (count($includeNodes));
    }
}

