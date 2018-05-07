<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Executables;

use Slothsoft\Farah\Module\FarahUrl\FarahUrl;
use Slothsoft\Farah\Module\FarahUrl\FarahUrlArguments;
use Slothsoft\Farah\Module\FarahUrl\FarahUrlStreamIdentifier;
use Slothsoft\Farah\Module\Node\Asset\AssetInterface;
use Slothsoft\Farah\Module\Results\ResultInterface;
use Slothsoft\Farah\Module\Results\ResultInterfacePlusXml;

interface ExecutableInterface
{

    public function init(AssetInterface $ownerAsset, FarahUrlArguments $args);

    /**
     * The Farah URL that represents this operation.
     *
     * @return string
     */
    public function getId(): string;

    /**
     * Create a FarahUrl for this operation, with stream set as supplied.
     *
     * @param FarahUrlStreamIdentifier|string $fragment
     * @return FarahUrl
     */
    public function createUrl($fragment = null): FarahUrl;

    /**
     * Create the result of this executable, in the representation type specified.
     *
     * @param FarahUrlStreamIdentifier|string $type
     * @return ResultInterface
     */
    public function lookupResult($type): ResultInterface;

    public function lookupDefaultResult(): ResultInterface;

    public function lookupXmlResult(): ResultInterfacePlusXml;
}

