<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\LinkDecorator;

use Slothsoft\Core\DOMHelper;
use Slothsoft\Farah\FarahUrl\FarahUrl;
use DOMDocument;

/**
 *
 * @author Daniel Schulz
 *        
 */
class SvgDecorator implements LinkDecoratorInterface {

    private $namespace;

    private $targetDocument;

    private $rootNode;

    public function setNamespace(string $namespace) {
        $this->namespace = $namespace;
    }

    public function setTarget(DOMDocument $document) {
        $this->targetDocument = $document;

        $this->rootNode = $document->getElementsByTagNameNS($this->namespace, 'defs')->item(0) ?? $document->documentElement;
    }

    public function linkStylesheets(FarahUrl ...$stylesheets) {
        foreach ($stylesheets as $url) {
            $href = str_replace('farah://', '/', (string) $url);

            $node = $this->targetDocument->createProcessingInstruction('xml-stylesheet', sprintf('type="text/css" href="%s"', $href));
            $this->targetDocument->insertBefore($node, $this->targetDocument->firstChild);
        }
    }

    public function linkScripts(FarahUrl ...$scripts) {
        foreach ($scripts as $url) {
            $href = str_replace('farah://', '/', (string) $url);

            $node = $this->targetDocument->createElementNS($this->namespace, 'script');
            $node->setAttributeNS(DOMHelper::NS_XLINK, 'xlink:href', $href);
            $node->setAttribute('defer', 'defer');
            $this->rootNode->appendChild($node);
        }
    }

    public function linkModules(FarahUrl ...$modules) {
        foreach ($modules as $url) {
            $href = str_replace('farah://', '/', (string) $url);

            $node = $this->targetDocument->createElementNS($this->namespace, 'script');
            $node->setAttributeNS(DOMHelper::NS_XLINK, 'xlink:href', $href);
            $node->setAttribute('type', 'module');
            $this->rootNode->appendChild($node);
        }
    }
}

