<?php
declare(strict_types = 1);

namespace Slothsoft\Farah\RequestStrategy;

use DOMDocument;
use Exception;
use Slothsoft\Core\IO\Writable\Delegates\DOMWriterFromDocumentDelegate;
use Slothsoft\Farah\Exception\HttpDownloadAssetException;
use Slothsoft\Farah\Exception\HttpDownloadException;
use Slothsoft\Farah\Exception\HttpDownloadExecutableException;
use Slothsoft\Farah\FarahUrl\FarahUrlArguments;
use Slothsoft\Farah\FarahUrl\FarahUrlStreamIdentifier;
use Slothsoft\Farah\Module\Asset\AssetInterface;
use Slothsoft\Farah\Module\Asset\ExecutableBuilderStrategy\ExecutableBuilderStrategyInterface;
use Slothsoft\Farah\Module\Executable\ExecutableInterface;
use Slothsoft\Farah\Module\Executable\ExecutableStrategies;
use Slothsoft\Farah\Module\Executable\ResultBuilderStrategy\DOMWriterResultBuilder;
use Slothsoft\Farah\Module\Executable\ResultBuilderStrategy\FileInfoResultBuilder;
use Slothsoft\Farah\Module\Executable\ResultBuilderStrategy\ResultBuilderStrategyInterface;
use Slothsoft\Farah\Module\Result\Result;
use Slothsoft\Farah\Module\Result\ResultInterface;
use Slothsoft\Farah\Module\Result\ResultStrategies;
use SplFileInfo;

/**
 * Local Archive-style example: ordinary requests render a listing, while
 * download/show requests interrupt the enclosing page and select a file.
 */
final class DownloadArchiveBuilder implements ExecutableBuilderStrategyInterface, ResultBuilderStrategyInterface {
    /** Allows the direct-result test to verify that the response uses this exact result. */
    public static ?ResultInterface $selectedResult = null;
    
    /** @throws Exception */
    public function buildExecutableStrategies(AssetInterface $context, FarahUrlArguments $args): ExecutableStrategies {
        if ($args->get('stage', 'asset') === 'asset' && $args->get('action', '') !== '') {
            // This is the pattern used by server-archive's executable builder.
            throw new HttpDownloadAssetException(new ExecutableStrategies($this->fileBuilder()), $args->get('action') === 'show');
        }
        return new ExecutableStrategies($this);
    }
    
    public function isDifferentFromDefault(FarahUrlStreamIdentifier $type): bool {
        return false;
    }
    
    /** @throws Exception */
    public function buildResultStrategies(ExecutableInterface $context, FarahUrlStreamIdentifier $type): ResultStrategies {
        $args = $context->getUrlArguments();
        if ($args->get('action', '') === '') {
            $writer = new DOMWriterFromDocumentDelegate(static function (): DOMDocument {
                $document = new DOMDocument();
                $document->loadXML('<archive><file name="archive.bin" /></archive>');
                return $document;
            });
            return (new DOMWriterResultBuilder($writer, 'listing'))->buildResultStrategies($context, $type);
        }
        $builder = $this->fileBuilder();
        $inline = $args->get('action') === 'show';
        switch ($args->get('stage')) {
            case 'asset-from-result':
                // Regression example for Executable's replacement result-builder path.
                throw new HttpDownloadAssetException(new ExecutableStrategies($builder), $inline);
            case 'result':
                // At this stage the caller can already supply ResultStrategies.
                throw new HttpDownloadExecutableException($builder->buildResultStrategies($context, $type), $inline);
            default:
                // A caller holding a complete result can use the final exception directly.
                self::$selectedResult = new Result($context, $type, $builder->buildResultStrategies($context, $type));
                throw new HttpDownloadException(self::$selectedResult, $inline);
        }
    }
    
    /** @throws Exception */
    private function fileBuilder(): FileInfoResultBuilder {
        // Keep binary bytes canonical in a text fixture; decode to a managed local file.
        $file = temp_dir(__CLASS__) . DIRECTORY_SEPARATOR . 'archive.bin';
        $encoded = file_get_contents(dirname(__DIR__, 2) . '/test-files/http-downloads/payload.base64');
        file_put_contents($file, base64_decode($encoded, true));
        return new FileInfoResultBuilder(new SplFileInfo($file));
    }
}
