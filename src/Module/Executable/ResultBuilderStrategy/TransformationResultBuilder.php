<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Executable\ResultBuilderStrategy;

use Slothsoft\Core\IO\Writable\Decorators\DOMWriterMemoryCache;
use Slothsoft\Farah\Dictionary;
use Slothsoft\Farah\FarahUrl\FarahUrlStreamIdentifier;
use Slothsoft\Farah\LinkDecorator\DecoratedDOMWriter;
use Slothsoft\Farah\Module\Module;
use Slothsoft\Farah\Module\DOMWriter\AssetDocumentDOMWriter;
use Slothsoft\Farah\Module\DOMWriter\AssetFragmentDOMWriter;
use Slothsoft\Farah\Module\DOMWriter\AssetManifestDOMWriter;
use Slothsoft\Farah\Module\DOMWriter\TransformationDOMWriter;
use Slothsoft\Farah\Module\DOMWriter\TranslationDOMWriter;
use Slothsoft\Farah\Module\Executable\Executable;
use Slothsoft\Farah\Module\Executable\ExecutableInterface;
use Slothsoft\Farah\Module\Result\ResultStrategies;
use Slothsoft\Farah\Module\Result\StreamBuilderStrategy\DOMWriterStreamBuilder;

class TransformationResultBuilder implements ResultBuilderStrategyInterface {

    private static function resultIsDefault(): FarahUrlStreamIdentifier {
        return Executable::resultIsDefault();
    }

    private static function resultIsXslSource(): FarahUrlStreamIdentifier {
        return FarahUrlStreamIdentifier::createFromString('xsl-source');
    }

    private static function resultIsXslTemplate(): FarahUrlStreamIdentifier {
        return FarahUrlStreamIdentifier::createFromString('xsl-template');
    }

    private $getUseInstructions;

    private $getLinkInstructions;

    private $translateResult;

    public function __construct(callable $getUseInstructions, callable $getLinkInstructions, bool $translateResult = true) {
        $this->getUseInstructions = $getUseInstructions;
        $this->getLinkInstructions = $getLinkInstructions;
        $this->translateResult = $translateResult;
    }

    public function buildResultStrategies(ExecutableInterface $context, FarahUrlStreamIdentifier $type): ResultStrategies {
        $instructions = ($this->getUseInstructions)();

        if ($instructions->templateUrl and $type === static::resultIsXslTemplate()) {
            $writer = Module::resolveToDOMWriter($instructions->templateUrl->withFragment('xml'));
        } else {
            $writer = new AssetFragmentDOMWriter($instructions->rootUrl);

            foreach ($instructions->manifestUrls as $url) {
                $writer->appendChild(new AssetManifestDOMWriter($url));
            }

            foreach ($instructions->documentUrls as $url) {
                $writer->appendChild(new AssetDocumentDOMWriter($url));
            }

            if ($instructions->templateUrl and $type !== static::resultIsXslSource()) {
                $template = Module::resolveToDOMWriter($instructions->templateUrl->withFragment('xml'));
                $writer = new TransformationDOMWriter($writer, $template);
                if ($this->translateResult) {
                    $writer = new TranslationDOMWriter($writer, Dictionary::getInstance(), $instructions->rootUrl);
                }
            }

            if ($type === static::resultIsDefault()) {
                // default result is the root transformation, so we gotta add all <link> and <script> elements
                $instructions = ($this->getLinkInstructions)();
                if (! $instructions->isEmpty()) {
                    $writer = new DecoratedDOMWriter($writer, $instructions->stylesheetUrls, $instructions->scriptUrls, $instructions->moduleUrls);
                }
            }
        }

        $writer = new DOMWriterMemoryCache($writer);

        $streamBuilder = new DOMWriterStreamBuilder($writer, 'transformation');
        return new ResultStrategies($streamBuilder);
    }
}

