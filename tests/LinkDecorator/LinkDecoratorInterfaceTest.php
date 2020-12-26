<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\LinkDecorator;

use PHPUnit\Framework\TestCase;

/**
 * LinkDecoratorInterfaceTest
 *
 * @see LinkDecoratorInterface
 *
 * @todo auto-generated
 */
class LinkDecoratorInterfaceTest extends TestCase {

    public function testInterfaceExists(): void {
        $this->assertTrue(interface_exists(LinkDecoratorInterface::class), "Failed to load interface 'Slothsoft\Farah\LinkDecorator\LinkDecoratorInterface'!");
    }
}