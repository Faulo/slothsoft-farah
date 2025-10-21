<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Schema;

use DOMDocument;
use Exception;

class SchemaLocator {
    
    // attributes
    public const ATTR_VERSION = 'version';
    
    private const NAMESPACE_BASE = 'http://schema.slothsoft.net/';
    
    private const DEFAULT_VERSION = '1.0';
    
    public function findSchemaLocation(DOMDocument $document): ?string {
        if ($node = $document->documentElement) {
            if ($node->hasAttributeNS('http://www.w3.org/2001/XMLSchema-instance', 'noNamespaceSchemaLocation')) { //DOMHelper::NS_XSI
                return $node->getAttributeNS('http://www.w3.org/2001/XMLSchema-instance', 'noNamespaceSchemaLocation');
            }
            if ($ns = $node->namespaceURI) {
                if (strpos($ns, self::NAMESPACE_BASE) === 0) {
                    $version = $node->hasAttribute(self::ATTR_VERSION) ? $node->getAttribute(self::ATTR_VERSION) : self::DEFAULT_VERSION;
                    $schema = explode('/', substr($ns, strlen(self::NAMESPACE_BASE)));
                    if (count($schema) !== 2) {
                        throw new Exception("Invalid slothsoft schema: $ns");
                    }
                    $url = "farah://slothsoft@$schema[0]/schema/$schema[1]/$version";
                    return $url;
                }
            } else {
                switch ($node->localName) {
                    case 'testsuites':
                        return 'farah://slothsoft@schema/schema/junit/1.0';
                }
            }
        }
        return null;
    }
}
