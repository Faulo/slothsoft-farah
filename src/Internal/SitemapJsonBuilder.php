<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Internal;

use Slothsoft\Core\DOMHelper;
use Slothsoft\Core\IO\Writable\DOMWriterInterface;
use Slothsoft\Core\IO\Writable\StringWriterInterface;

/**
 *
 * @author Daniel Schulz
 *        
 */
final class SitemapJsonBuilder implements StringWriterInterface {
    
    private DOMWriterInterface $writer;
    
    public function __construct(DOMWriterInterface $writer) {
        $this->writer = $writer;
    }
    
    public function toString(): string {
        $document = $this->writer->toDocument();
        $xpath = DOMHelper::loadXPath($document, 0);
        
        $data = [];
        foreach ($xpath->query('//*[@uri]') as $node) {
            $data[] = $node->getAttribute('uri');
        }
        
        return json_encode($data);
    }
}