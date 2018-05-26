<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Executable;

use Slothsoft\Farah\FarahUrl\FarahUrl;
use Slothsoft\Farah\FarahUrl\FarahUrlStreamIdentifier;
use Slothsoft\Farah\Module\Result\ResultInterface;
use Slothsoft\Farah\FarahUrl\FarahUrlArguments;

interface ExecutableInterface
{
    
    public function getUrlArguments() : FarahUrlArguments;

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

    public function lookupXmlResult(): ResultInterface;
}

