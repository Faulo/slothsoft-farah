<?php
declare(strict_types = 1);

namespace Slothsoft\Farah\Internal;

use DOMElement;
use LogicException;
use Slothsoft\Core\DOMHelper;
use Slothsoft\Core\IO\Writable\DOMWriterInterface;
use Slothsoft\Core\IO\Writable\StringWriterInterface;

/**
 * String writer that serializes the current Farah sitemap as JSON.
 *
 * @author Daniel Schulz
 * @since 2026-01-14
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
        $nodes = $xpath->query('//*[@uri]');
        if ($nodes === false) {
            throw new LogicException('Failed to query sitemap URIs.');
        }
        foreach ($nodes as $node) {
            if (! $node instanceof DOMElement) {
                throw new LogicException('Expected a sitemap element.');
            }
            $data[] = $node->getAttribute('uri');
        }
        
        return json_encode($data);
    }
}
