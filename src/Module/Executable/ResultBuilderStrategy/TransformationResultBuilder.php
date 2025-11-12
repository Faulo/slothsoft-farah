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
    
    public function __construct(callable $getUseInstructions, callable $getLinkInstructions, bool $translateResult = true, bool $decorateResult = true, bool $cacheResult = false) {
        $this->getUseInstructions = Closure::fromCallable($getUseInstructions);
        $this->getLinkInstructions = Closure::fromCallable($getLinkInstructions);
        $this->translateResult = $translateResult;
        $this->decorateResult = $decorateResult;
        $this->cacheResult = $cacheResult;
    }
    
    private function getUseInstructionsTyped(): UseInstructionCollection {
        return ($this->getUseInstructions)();
    }
    
    private function getLinkInstructionsTyped(): LinkInstructionCollection {
        return ($this->getLinkInstructions)();
    }
    
    public function buildResultStrategies(ExecutableInterface $context, FarahUrlStreamIdentifier $type): ResultStrategies {
        $instructions = $this->getUseInstructionsTyped();
        
        if ($instructions->templateUrl and $type === static::resultIsXslTemplate()) {
            $writer = Module::resolveToDOMWriter($instructions->templateUrl->withStreamIdentifier(Executable::resultIsXml()));
        } else {
            $writer = new AssetFragmentDOMWriter($instructions->rootUrl);
            
            foreach ($instructions->dataWriters as $data) {
                $writer->appendChild($data);
            }
            
            if ($instructions->templateUrl and $type !== static::resultIsXslSource()) {
                $template = Module::resolveToDOMWriter($instructions->templateUrl->withStreamIdentifier(Executable::resultIsXml()));
                $writer = new TransformationDOMWriter($writer, $template);
                if ($this->translateResult) {
                    $writer = new TranslationDOMWriter($writer, Dictionary::getInstance(), $instructions->rootUrl);
                }
            }
            
            if ($this->decorateResult) {
                // add all <link> and <script> elements
                $instructions = $this->getLinkInstructionsTyped();
                if (! $instructions->isEmpty()) {
                    $writer = new DecoratedDOMWriter($writer, $instructions->stylesheetUrls, $instructions->scriptUrls, $instructions->moduleUrls, $instructions->contentUrls);
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

