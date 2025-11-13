<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Executable\ResultBuilderStrategy;

use Slothsoft\Core\IO\Writable\Decorators\DOMWriterMemoryCache;
use Slothsoft\Farah\Dictionary;
use Slothsoft\Farah\FarahUrl\FarahUrlStreamIdentifier;
use Slothsoft\Farah\LinkDecorator\DecoratedDOMWriter;
use Slothsoft\Farah\Module\Module;
use Slothsoft\Farah\Module\Asset\LinkInstructionCollection;
use Slothsoft\Farah\Module\Asset\UseInstructionCollection;
use Slothsoft\Farah\Module\DOMWriter\AssetFragmentDOMWriter;
use Slothsoft\Farah\Module\DOMWriter\TransformationDOMWriter;
use Slothsoft\Farah\Module\DOMWriter\TranslationDOMWriter;
use Slothsoft\Farah\Module\Executable\Executable;
use Slothsoft\Farah\Module\Executable\ExecutableInterface;
use Slothsoft\Farah\Module\Result\ResultStrategies;
use Slothsoft\Farah\Module\Result\StreamBuilderStrategy\DOMWriterStreamBuilder;
use Closure;

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
    
    private Closure $getUseInstructions;
    
    private Closure $getLinkInstructions;
    
    private bool $translateResult;
    
    private bool $decorateResult;
    
    private bool $cacheResult;
    
    public function __construct(callable $getUseInstructions, callable $getLinkInstructions, bool $translateResult = true, bool $decorateResult = true, bool $cacheResult = true) {
        $this->getUseInstructions = Closure::fromCallable($getUseInstructions);
        $this->getLinkInstructions = Closure::fromCallable($getLinkInstructions);
        $this->translateResult = $translateResult;
        $this->decorateResult = $decorateResult;
        $this->cacheResult = $cacheResult;
    }
    
    private ?UseInstructionCollection $useInstructions = null;
    
    public function getUseInstructionsTyped(): UseInstructionCollection {
        $this->useInstructions ??= ($this->getUseInstructions)();
        return $this->useInstructions;
    }
    
    private ?LinkInstructionCollection $linkInstructions = null;
    
    public function getLinkInstructionsTyped(): LinkInstructionCollection {
        $this->linkInstructions ??= ($this->getLinkInstructions)();
        return $this->linkInstructions;
    }
    
    public function buildResultStrategies(ExecutableInterface $context, FarahUrlStreamIdentifier $type): ResultStrategies {
        $useInstructions = $this->getUseInstructionsTyped();
        
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
                    $writer = new TranslationDOMWriter($writer, Dictionary::getInstance(), $useInstructions->rootUrl);
                }
            }
            
            if ($this->decorateResult) {
                $linkInstructions = $this->getLinkInstructionsTyped();
                // add all <link> and <script> elements
                if (! $linkInstructions->isEmpty()) {
                    $writer = new DecoratedDOMWriter($writer, $linkInstructions->stylesheetUrls, $linkInstructions->scriptUrls, $linkInstructions->moduleUrls, $linkInstructions->contentUrls);
                }
            }
        }
        
        if ($this->cacheResult) {
            $writer = new DOMWriterMemoryCache($writer);
        }
        
        $streamBuilder = new DOMWriterStreamBuilder($writer, 'transformation');
        return new ResultStrategies($streamBuilder);
    }
}

