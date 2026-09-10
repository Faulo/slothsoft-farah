<?php
declare(strict_types = 1);

namespace Slothsoft\Farah\Sites;

use DOMDocument;
use DOMElement;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Slothsoft\Core\DOMHelper;

/**
 * DomainTest
 *
 * @see Domain
 *
 */
final class DomainTest extends TestCase {
    
    /**
     *
     * @test
     */
    public function testClassExists(): void {
        $this->assertTrue(class_exists(Domain::class), "Failed to load class 'Slothsoft\Farah\Sites\Domain'!");
    }

    public function testSetCurrentPageNodeReplacesAllMarkers(): void {
        $document = new DOMDocument();
        $document->loadXML('<domain xmlns="' . DOMHelper::NS_FARAH_SITES . '" current="1"><page name="first" current="1"/><page name="second"/></domain>');
        $domain = new Domain($document);
        $pageNode = $domain->getXPath()->query('//*[@name="second"]')->item(0);
        $this->assertInstanceOf(DOMElement::class, $pageNode);

        $domain->setCurrentPageNode($pageNode);

        $currentNodes = $domain->getXPath()->query('//*[@current]');
        $this->assertCount(1, $currentNodes);
        $this->assertSame($pageNode, $currentNodes->item(0));
    }

    public function testSetCurrentPageNodeRejectsForeignNode(): void {
        $domainDocument = new DOMDocument();
        $domainDocument->loadXML('<domain xmlns="' . DOMHelper::NS_FARAH_SITES . '"/>');
        $foreignDocument = new DOMDocument();
        $foreignDocument->loadXML('<page xmlns="' . DOMHelper::NS_FARAH_SITES . '"/>');

        $this->expectException(InvalidArgumentException::class);
        (new Domain($domainDocument))->setCurrentPageNode($foreignDocument->documentElement);
    }
}
