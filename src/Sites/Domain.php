<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Sites;

use Slothsoft\Core\DOMHelper;
use Slothsoft\Farah\FarahUrl\FarahUrl;
use Slothsoft\Farah\FarahUrl\FarahUrlArguments;
use Slothsoft\Farah\FarahUrl\FarahUrlAuthority;
use Slothsoft\Farah\Module\Module;
use DOMDocument;
use DOMElement;
use DOMXPath;

/**
 *
 * @author Daniel Schulz
 *        
 */
final class Domain {
    
    public const CURRENT_SITEMAP = 'farah://slothsoft@farah/current-sitemap';
    
    public static function createWithDefaultSitemap(): Domain {
        $url = FarahUrl::createFromReference(self::CURRENT_SITEMAP);
        return new self(Module::resolveToDOMWriter($url)->toDocument());
    }
    
    // tags
    public const TAG_SITEMAP = 'sitemap';
    
    public const TAG_DOMAIN = 'domain';
    
    public const TAG_PAGE = 'page';
    
    public const TAG_FILE = 'file';
    
    public const TAG_INCLUDE_PAGES = 'include-pages';
    
    // attributes
    public const ATTR_NAME = 'name';
    
    public const ATTR_REFERENCE = 'ref';
    
    public const ATTR_CURRENT_PAGE = 'current';
    
    public const ATTR_REDIRECT = 'redirect';
    
    public const ATTR_URI = 'uri';
    
    private DOMDocument $document;
    
    private DOMXPath $xpath;
    
    public function __construct(DOMDocument $document) {
        $this->document = $document;
        $this->xpath = DOMHelper::loadXPath($this->document, DOMHelper::XPATH_SLOTHSOFT | DOMHelper::XPATH_PHP);
    }
    
    public function getXPath(): DOMXPath {
        return $this->xpath;
    }
    
    public function getDomainNode(): DOMElement {
        return $this->document->documentElement;
    }
    
    public function getCurrentPageNode(): ?DOMElement {
        $currentNodes = $this->xpath->evaluate('//*[@current]');
        return $currentNodes->length ? $currentNodes->item(0) : null;
    }
    
    public function setCurrentPageNode(DOMElement $pageNode): void {
        if ($oldNode = $this->getCurrentPageNode()) {
            if ($oldNode === $pageNode) {
                return;
            }
            $oldNode->removeAttribute(self::ATTR_CURRENT_PAGE);
        }
        
        $pageNode->setAttribute(self::ATTR_CURRENT_PAGE, '1');
    }
    
    public function clearCurrentPageNode(): void {
        if ($oldNode = $this->getCurrentPageNode()) {
            $oldNode->removeAttribute(self::ATTR_CURRENT_PAGE);
        }
    }
    
    public function lookupAssetUrl(DOMElement $dataNode, array $args = []): FarahUrl {
        $vendorName = $this->findVendorName($dataNode);
        $moduleName = $this->findModuleName($dataNode);
        $args += $this->findParameters($dataNode);
        $ref = $dataNode->getAttribute('ref');
        
        return FarahUrl::createFromReference($ref, FarahUrl::createFromComponents(FarahUrlAuthority::createFromVendorAndModule($vendorName, $moduleName), null, FarahUrlArguments::createFromValueList($args)));
    }
    
    private function findModuleName(DOMElement $node): string {
        return $this->xpath->evaluate('string(ancestor-or-self::*[@module][1]/@module)', $node);
    }
    
    private function findVendorName(DOMElement $node): string {
        return $this->xpath->evaluate('string(ancestor-or-self::*[@vendor][1]/@vendor)', $node);
    }
    
    private function findParameters(DOMElement $node): array {
        $ret = [];
        foreach ($this->xpath->evaluate('ancestor-or-self::*/sfm:param', $node) as $paramNode) {
            $ret[$paramNode->getAttribute('name')] = $paramNode->getAttribute('value');
        }
        return $ret;
    }
}

