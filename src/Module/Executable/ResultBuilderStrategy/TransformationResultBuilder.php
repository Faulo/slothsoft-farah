<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Executable\ResultBuilderStrategy;

use Slothsoft\Core\IO\Writable\Decorators\DOMWriterMemoryCache;
use Slothsoft\Farah\Dictionary;
use Slothsoft\Farah\FarahUrl\FarahUrlStreamIdentifier;
use Slothsoft\Farah\LinkDecorator\DecoratedDOMWriter;
use Slothsoft\Farah\Module\Module;
use Slothsoft\Farah\Module\DOMWriter\AssetFragmentDOMWriter;
use Slothsoft\Farah\Module\DOMWriter\TransformationDOMWriter;
use Slothsoft\Farah\Module\DOMWriter\TranslationDOMWriter;
use Slothsoft\Farah\Module\DOMWriter\TranslationDOMWriter2;
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
    
    public function isDifferentFromDefault(FarahUrlStreamIdentifier $type): bool {
        return $type === self::resultIsXslSource() or $type === self::resultIsXslTemplate();
    }
    
    private bool $translateResult;
    
    private bool $decorateResult;
    
    private bool $cacheResult;
    
    public function __construct(bool $translateResult = true, bool $decorateResult = true, bool $cacheResult = true) {
        $this->translateResult = $translateResult;
        $this->decorateResult = $decorateResult;
        $this->cacheResult = $cacheResult;
    }
    
    public function buildResultStrategies(ExecutableInterface $context, FarahUrlStreamIdentifier $type): ResultStrategies {
        $useInstructions = $context->lookupUseInstructions();
        
        if ($useInstructions->templateUrl and $type === static::resultIsXslTemplate()) {
            $writer = Module::resolveToDOMWriter($useInstructions->templateUrl->withStreamIdentifier(Executable::resultIsXml()));
        } else {
            $writer = new AssetFragmentDOMWriter($useInstructions->rootUrl);
            
            foreach ($useInstructions->dataWriters as $data) {
                $writer->appendChild($data);
            }
            
            if ($useInstructions->templateUrl and $type !== static::resultIsXslSource()) {
                $template = Module::resolveToDOMWriter($useInstructions->templateUrl->withStreamIdentifier(Executable::resultIsXml()));
                $writer = new TransformationDOMWriter($writer, $template);
                if ($this->translateResult) {
                    $linkInstructions = $context->lookupLinkInstructions();
                    // translate sfd:dict attributes
                    if ($linkInstructions->dictionaryUrls->isEmpty()) {
                        $writer = new TranslationDOMWriter($writer, Dictionary::getInstance(), $useInstructions->rootUrl);
                    } else {
                        $writer = new TranslationDOMWriter2($writer, Dictionary::getInstance(), $linkInstructions->dictionaryUrls);
                    }
                }
            }
            
            if ($this->decorateResult) {
                $linkInstructions = $context->lookupLinkInstructions();
                // add all <link> and <script> elements
                $writer = new DecoratedDOMWriter($writer, $linkInstructions->stylesheetUrls, $linkInstructions->scriptUrls, $linkInstructions->moduleUrls, $linkInstructions->contentUrls);
            }
        }
        
        if ($this->cacheResult) {
            $writer = new DOMWriterMemoryCache($writer);
        }
        
        $streamBuilder = new DOMWriterStreamBuilder($writer, 'transformation');
        return new ResultStrategies($streamBuilder);
    }
}

