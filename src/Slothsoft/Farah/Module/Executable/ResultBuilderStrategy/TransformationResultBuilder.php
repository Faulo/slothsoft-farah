<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Executable\ResultBuilderStrategy;

use Slothsoft\Farah\Dictionary;
use Slothsoft\Farah\FarahUrl\FarahUrlStreamIdentifier;
use Slothsoft\Farah\LinkDecorator\DecoratedDOMWriter;
use Slothsoft\Farah\Module\Asset\AssetInterface;
use Slothsoft\Farah\Module\DOMWriter\ManifestDOMWriter;
use Slothsoft\Farah\Module\DOMWriter\DocumentDOMWriter;
use Slothsoft\Farah\Module\DOMWriter\FragmentDOMWriter;
use Slothsoft\Farah\Module\DOMWriter\TransformationDOMWriter;
use Slothsoft\Farah\Module\DOMWriter\TranslationDOMWriter;
use Slothsoft\Farah\Module\Executable\ExecutableInterface;
use Slothsoft\Farah\Module\Result\ResultStrategies;
use Slothsoft\Farah\Module\Result\StreamBuilderStrategy\DOMWriterStreamBuilder;

class TransformationResultBuilder implements ResultBuilderStrategyInterface
{
    private static function resultIsDefault() : FarahUrlStreamIdentifier {
        return FarahUrlStreamIdentifier::createFromString('');
    }
    private static function resultIsXslSource() : FarahUrlStreamIdentifier {
        return FarahUrlStreamIdentifier::createFromString('xsl-source');
    }
    private static function resultIsXslTemplate() : FarahUrlStreamIdentifier {
        return FarahUrlStreamIdentifier::createFromString('xsl-template');
    }
    
    private $asset;
    
    public function __construct(AssetInterface $asset)
    {
        $this->asset = $asset;
    }
    public function buildResultStrategies(ExecutableInterface $context, FarahUrlStreamIdentifier $type) : ResultStrategies
    {
        $instructions = $this->asset->getUseInstructions();
        
        if ($instructions->templateAsset and $type === static::resultIsXslTemplate()) {
            $executable = $instructions->templateAsset->lookupExecutable($context->getUrlArguments());
            $writer = $executable->lookupXmlResult();
        } else {
            $writer = new FragmentDOMWriter($this->asset);
            
            foreach ($instructions->manifestAssets as $asset) {
                $writer->appendChild(new ManifestDOMWriter($asset));
            }
            
            foreach ($instructions->documentAssets as $asset) {
                $writer->appendChild(new DocumentDOMWriter($asset, $context->getUrlArguments()));
            }
            
            if ($instructions->templateAsset and $type !== static::resultIsXslSource()) {
                $executable = $instructions->templateAsset->lookupExecutable($context->getUrlArguments());
                $result = $executable->lookupXmlResult();
                $writer = new TransformationDOMWriter($writer, $result);
                $writer = new TranslationDOMWriter($writer, Dictionary::getInstance(), $context->createUrl());
            }
            
            if ($type === static::resultIsDefault()) {
                // default result is the root transformation, so we gotta add all <link> and <script> elements
                $instructions = $this->asset->getLinkInstructions();
                if (!($instructions->stylesheetAssets->isEmpty() and $instructions->scriptAssets->isEmpty())) {
                    $writer = new DecoratedDOMWriter($writer, $instructions->stylesheetAssets, $instructions->scriptAssets);
                }
            }
        }
        
        $streamBuilder = new DOMWriterStreamBuilder($writer);
        return new ResultStrategies($streamBuilder);
    }
}

