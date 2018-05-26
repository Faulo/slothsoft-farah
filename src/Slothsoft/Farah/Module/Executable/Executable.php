<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Executable;

use Slothsoft\Farah\FarahUrl\FarahUrl;
use Slothsoft\Farah\FarahUrl\FarahUrlArguments;
use Slothsoft\Farah\FarahUrl\FarahUrlStreamIdentifier;
use Slothsoft\Farah\Module\Asset\AssetInterface;
use Slothsoft\Farah\Module\Result\Result;
use Slothsoft\Farah\Module\Result\ResultContainer;
use Slothsoft\Farah\Module\Result\ResultInterface;
use Slothsoft\Farah\Module\Result\XmlResult;

class Executable implements ExecutableInterface
{
    const RESULT_IS_DEFAULT = '';
    
    public final function resultIsDefault(): FarahUrlStreamIdentifier
    {
        return FarahUrlStreamIdentifier::createFromString(self::RESULT_IS_DEFAULT);
    }
    
    const RESULT_IS_XML = 'xml';
    
    public final function resultIsXml(): FarahUrlStreamIdentifier
    {
        return FarahUrlStreamIdentifier::createFromString(self::RESULT_IS_XML);
    }
    
    /**
     * @var AssetInterface
     */
    private $ownerAsset;
    
    /**
     * @var FarahUrlArguments
     */
    private $args;
    
    /**
     * @var ExecutableStrategies
     */
    private $strategies;
    
    /**
     * @var ResultContainer
     */
    private $results;
    
    public function __construct(AssetInterface $ownerAsset, FarahUrlArguments $args, ExecutableStrategies $strategies)
    {
        $this->ownerAsset = $ownerAsset;
        $this->args = $args;
        $this->strategies = $strategies;
        $this->results = new ResultContainer();
    }
    
    public function getUrlArguments() : FarahUrlArguments {
        return $this->args;
    }
    public function lookupResult($type): ResultInterface
    {
        if (is_string($type)) {
            $type = FarahUrlStreamIdentifier::createFromString($type);
        }
        if (! $this->results->has($type)) {
            $this->results->put($type, $this->createResult($type));
        }
        return $this->results->get($type);
    }
    private function createResult(FarahUrlStreamIdentifier $type) : ResultInterface {
        $strategies = $this->strategies->resultBuilder->buildResultStrategies($this, $type);
        
        if ($type === static::resultIsXml()) {
            $result = new XmlResult($this, $type, $strategies);
        } else {
            $result = new Result($this, $type, $strategies);
        }
        
        return $result;
    }
    
    public function lookupDefaultResult(): ResultInterface
    {
        return $this->lookupResult($this->resultIsDefault());
    }

    public function lookupXmlResult(): ResultInterface
    {
        return $this->lookupResult($this->resultIsXml());
    }

    public function createUrl($fragment = null): FarahUrl
    {
        return $this->ownerAsset->createUrl($this->args, $fragment);
    }
}

