<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\ModuleTests;

use PHPUnit\Framework\TestCase;
use DOMDocument;
use DOMElement;
use Throwable;

class AbstractTestCase extends TestCase {

    protected function failException(Throwable $e): void {
        $this->fail(sprintf('%s:%s%s%s%s', get_class($e), PHP_EOL, $e->getMessage(), PHP_EOL, $e->getTraceAsString()));
    }

    protected function getObjectProperty(object $target, string $name) {
        $getProperty = function (string $name) {
            return $this->$name;
        };
        $getProperty = $getProperty->bindTo($target, get_class($target));
        return $getProperty($name);
    }

    protected function getObjectMethod(object $target, string $name, ...$args) {
        $getProperty = function (string $name, $args) {
            return $this->$name(...$args);
        };
        $getProperty = $getProperty->bindTo($target, get_class($target));
        return $getProperty($name, $args);
    }

    protected function findSchemaLocation(DOMDocument $document): ?string {
        $node = $document->documentElement;
        $this->assertInstanceOf(DOMElement::class, $node);
        $ns = $node->namespaceURI;

        if ($ns !== null) {
            if (strpos($ns, 'http://schema.slothsoft.net/') === 0) {
                $version = $node->hasAttribute('version') ? $node->getAttribute('version') : '1.0';
                $schema = explode('/', substr($ns, strlen('http://schema.slothsoft.net/')));
                $this->assertEquals(2, count($schema), "Invalid slothsoft schema: $ns");
                $url = "farah://slothsoft@$schema[0]/schema/$schema[1]/$version";
                return $url;
            }
        }

        return null;
    }

    protected function assertSchema(DOMDocument $document, string $schema): void {
        try {
            // echo PHP_EOL . $schema . PHP_EOL . DOMHelper::loadDocument($schema)->documentURI . PHP_EOL . file_get_contents($schema) . PHP_EOL . PHP_EOL;

            $validateResult = $document->schemaValidate($schema);
        } catch (Throwable $e) {
            $validateResult = false;
            $this->failException($e);
        }

        $this->assertTrue($validateResult, "Slothsoft document '$document->documentURI' did not pass vaidation with '$schema'!");
    }
}

