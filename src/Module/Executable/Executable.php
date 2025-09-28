<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Executable;

use Slothsoft\Farah\Exception\HttpDownloadException;
use Slothsoft\Farah\Exception\HttpDownloadExecutableException;
use Slothsoft\Farah\FarahUrl\FarahUrl;
use Slothsoft\Farah\FarahUrl\FarahUrlArguments;
use Slothsoft\Farah\FarahUrl\FarahUrlStreamIdentifier;
use Slothsoft\Farah\Module\Asset\AssetInterface;
use Slothsoft\Farah\Module\Result\Result;
use Slothsoft\Farah\Module\Result\ResultContainer;
use Slothsoft\Farah\Module\Result\ResultInterface;

class Executable implements ExecutableInterface {
    
    const RESULT_IS_DEFAULT = '';
    
    public static function resultIsDefault(): FarahUrlStreamIdentifier {
        return FarahUrlStreamIdentifier::createFromString(self::RESULT_IS_DEFAULT);
    }
    
    const RESULT_IS_XML = 'xml';
    
    public static function resultIsXml(): FarahUrlStreamIdentifier {
        return FarahUrlStreamIdentifier::createFromString(self::RESULT_IS_XML);
    }
    
    private AssetInterface $ownerAsset;
    
    private FarahUrlArguments $args;
    
    private ExecutableStrategies $strategies;
    
    private ResultContainer $results;
    
    public function __construct(AssetInterface $ownerAsset, FarahUrlArguments $args, ExecutableStrategies $strategies) {
        $this->ownerAsset = $ownerAsset;
        $this->args = $args;
        $this->strategies = $strategies;
        $this->results = new ResultContainer();
    }
    
    public function getUrlArguments(): FarahUrlArguments {
        return $this->args;
    }
    
    public function lookupResult($type): ResultInterface {
        if (is_string($type)) {
            $type = FarahUrlStreamIdentifier::createFromString($type);
        }
        if (! $this->results->has($type)) {
            $this->results->put($type, $this->createResult($type));
        }
        return $this->results->get($type);
    }
    
    private function createResult(FarahUrlStreamIdentifier $type): ResultInterface {
        try {
            $strategies = $this->strategies->resultBuilder->buildResultStrategies($this, $type);
            $result = new Result($this, $type, $strategies);
            return $result;
        } catch (HttpDownloadExecutableException $e) {
            $strategies = $e->getStrategies();
            $result = new Result($this, $type, $strategies);
            throw new HttpDownloadException($result, $e->isInline());
        }
    }
    
    public function lookupDefaultResult(): ResultInterface {
        return $this->lookupResult(static::resultIsDefault());
    }
    
    public function lookupXmlResult(): ResultInterface {
        return $this->lookupResult(static::resultIsXml());
    }
    
    public function createUrl($fragment = null): FarahUrl {
        return $this->ownerAsset->createUrl($this->args, $fragment);
    }
}

