<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Asset\ExecutableBuilderStrategy;

use Slothsoft\Farah\Dictionary;
use Slothsoft\Farah\FarahUrl\FarahUrlArguments;
use Slothsoft\Farah\FarahUrl\FarahUrlPath;
use Slothsoft\Farah\Module\Asset\AssetInterface;
use Slothsoft\Farah\Module\Executable\ExecutableStrategies;
use Slothsoft\Farah\Module\Executable\ResultBuilderStrategy\FromManifestInstructionBuilder;
use Slothsoft\Farah\Module\Executable\ResultBuilderStrategy\TransformationResultBuilder;
use Slothsoft\Farah\Module\Manifest\ManifestInterface;

class FromManifestExecutableBuilder implements ExecutableBuilderStrategyInterface {
    
    private const DICTIONARY_PATH = '/dictionary';
    
    private static function canTranslate(ManifestInterface $manifest, FarahUrlPath $path): bool {
        if (count(Dictionary::getSupportedLanguages()) === 0) {
            // no languages available to translate to
            return false;
        }
        
        if (stripos((string) $path, self::DICTIONARY_PATH) === 0) {
            // dictionaries don't get translated
            return false;
        }
        
        if (! file_exists((string) $manifest->createUrl(self::DICTIONARY_PATH))) {
            // current manifest doesn't have a dictionary to use
            return false;
        }
        
        return true;
    }
    
    public function buildExecutableStrategies(AssetInterface $context, FarahUrlArguments $args): ExecutableStrategies {
        $translateResult = self::canTranslate($context->getManifest(), $context->getUrlPath());
        $resultBuilder = new TransformationResultBuilder($translateResult);
        $instructionBuilder = new FromManifestInstructionBuilder();
        return new ExecutableStrategies($resultBuilder, $instructionBuilder);
    }
}

