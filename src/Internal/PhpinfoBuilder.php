<?php
declare(strict_types = 1);

namespace Slothsoft\Farah\Internal;

use DOMDocument;
use Generator;
use Slothsoft\Core\DOMHelper;
use Slothsoft\Core\IO\Writable\Delegates\ChunkWriterFromChunksDelegate;
use Slothsoft\Core\IO\Writable\Delegates\DOMWriterFromDocumentDelegate;
use Slothsoft\Farah\FarahUrl\FarahUrlArguments;
use Slothsoft\Farah\Module\Asset\AssetInterface;
use Slothsoft\Farah\Module\Asset\ExecutableBuilderStrategy\ExecutableBuilderStrategyInterface;
use Slothsoft\Farah\Module\Executable\ExecutableStrategies;
use Slothsoft\Farah\Module\Executable\ResultBuilderStrategy\ChunkWriterResultBuilder;
use Slothsoft\Farah\Module\Executable\ResultBuilderStrategy\DOMWriterResultBuilder;

/**
 *
 * @author Daniel Schulz
 *
 */
class PhpinfoBuilder implements ExecutableBuilderStrategyInterface {
    
    private static function getContent(): string {
        ob_start();
        phpinfo();
        $data = ob_get_contents();
        ob_end_clean();
        return $data;
    }
    
    public function buildExecutableStrategies(AssetInterface $context, FarahUrlArguments $args): ExecutableStrategies {
        $resultBuilder = match (PHP_SAPI) {
            'cli' => new ChunkWriterResultBuilder(
                new ChunkWriterFromChunksDelegate(function (): Generator {
                    yield '<pre>\n';
                    yield htmlentities(self::getContent(), ENT_XML1 | ENT_DISALLOWED, 'UTF-8');
                    yield '</pre>';
                }), 'phpinfo.xhtml'),
            default => new DOMWriterResultBuilder(
                new DOMWriterFromDocumentDelegate(function (): DOMDocument {
                    $document = DOMHelper::parseDocument(self::getContent(), true);
                    foreach ($document->documentElement->attributes as $attr) {
                        $document->documentElement->removeAttributeNode($attr);
                    }
                    return $document;
                }), 'phpinfo'),
        };
        
        return new ExecutableStrategies($resultBuilder);
    }
}

