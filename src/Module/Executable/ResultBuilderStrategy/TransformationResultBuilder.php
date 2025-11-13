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
    
    public UseInstructionCollection $useInstructions;
    
    public LinkInstructionCollection $linkInstructions;
    
    private bool $translateResult;
    
    private bool $decorateResult;
    
    private bool $cacheResult;
    
    public function __construct(UseInstructionCollection $useInstructions, LinkInstructionCollection $linkInstructions, bool $translateResult = true, bool $decorateResult = true, bool $cacheResult = true) {
        $this->useInstructions = $useInstructions;
        $this->linkInstructions = $linkInstructions;
        $this->translateResult = $translateResult;
        $this->decorateResult = $decorateResult;
        $this->cacheResult = $cacheResult;
    }
    
    public function buildResultStrategies(ExecutableInterface $context, FarahUrlStreamIdentifier $type): ResultStrategies {
        if ($this->useInstructions->templateUrl and $type === static::resultIsXslTemplate()) {
            $writer = Module::resolveToDOMWriter($this->useInstructions->templateUrl->withStreamIdentifier(Executable::resultIsXml()));
        } else {
            $writer = new AssetFragmentDOMWriter($this->useInstructions->rootUrl);
            
            foreach ($this->useInstructions->dataWriters as $data) {
                $writer->appendChild($data);
            }
            
            if ($this->useInstructions->templateUrl and $type !== static::resultIsXslSource()) {
                $template = Module::resolveToDOMWriter($this->useInstructions->templateUrl->withStreamIdentifier(Executable::resultIsXml()));
                $writer = new TransformationDOMWriter($writer, $template);
                if ($this->translateResult) {
                    $writer = new TranslationDOMWriter($writer, Dictionary::getInstance(), $this->useInstructions->rootUrl);
                }
            }
            
            if ($this->decorateResult) {
                // add all <link> and <script> elements
                if (! $this->linkInstructions->isEmpty()) {
                    $writer = new DecoratedDOMWriter($writer, $this->linkInstructions->stylesheetUrls, $this->linkInstructions->scriptUrls, $this->linkInstructions->moduleUrls, $this->linkInstructions->contentUrls);
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

