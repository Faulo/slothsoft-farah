<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Executables;

use Slothsoft\Farah\Module\FarahUrl\FarahUrl;
use Slothsoft\Farah\Module\FarahUrl\FarahUrlArguments;
use Slothsoft\Farah\Module\FarahUrl\FarahUrlStreamIdentifier;
use Slothsoft\Farah\Module\Node\Asset\AssetInterface;
use Slothsoft\Farah\Module\Results\ResultInterface;
use Slothsoft\Farah\Module\Results\ResultInterfacePlusXml;

abstract class ExecutableBase implements ExecutableInterface
{
    const RESULT_IS_DEFAULT = '';
    protected static final function resultIsDefault() : FarahUrlStreamIdentifier {
        return FarahUrlStreamIdentifier::createFromString(self::RESULT_IS_DEFAULT);
    }
    const RESULT_IS_XML = 'xml';
    protected static final function resultIsXml() : FarahUrlStreamIdentifier {
        return FarahUrlStreamIdentifier::createFromString(self::RESULT_IS_XML);
    }
    
    private $ownerAsset;
    private $args;
    private $results;
    public function init(AssetInterface $ownerAsset, FarahUrlArguments $args)
    {
        if ($this->ownerAsset) {
            throw new \BadMethodCallException("Cannot initialize executable more than once.");
        }
        $this->ownerAsset = $ownerAsset;
        $this->args = $args;
        $this->results = [];
    }
    protected final function getOwnerAsset() : AssetInterface {
        return $this->ownerAsset;
    }
    protected final function getArguments() : FarahUrlArguments {
        return $this->args;
    }

    public final function lookupResult($type) : ResultInterface
    {
        if (is_string($type)) {
            $typeId = $type;
            $type = FarahUrlStreamIdentifier::createFromString($type);
        } else {
            $typeId = (string) $type;
        }
        if (!isset($this->results[$typeId])) {
            $this->results[$typeId] = $this->loadResult($type);
        }
        return $this->results[$typeId];
    }
    abstract protected function loadResult(FarahUrlStreamIdentifier $type) : ResultInterface;
    
    public final function lookupDefaultResult() : ResultInterface
    {
        return $this->lookupResult(self::resultIsXml());
    }
    
    public final function lookupXmlResult() : ResultInterfacePlusXml
    {
        return $this->lookupResult(self::resultIsXml());
    }

    public final function createUrl($fragment = null) : FarahUrl
    {
        return $this->ownerAsset->createUrl($this->args, $fragment);
    }

    public final function getId() : string
    {
        return (string) $this->createUrl();
    }

}

