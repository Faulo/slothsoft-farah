<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Internal;

use Slothsoft\Farah\HTTPRequest;
use Slothsoft\Farah\Module\FarahUrl\FarahUrl;
use Slothsoft\Farah\Module\Node\Asset\AssetImplementation;
use Slothsoft\Farah\Module\Results\ResultCatalog;
use Slothsoft\Farah\Module\Results\ResultInterface;

/**
 *
 * @author Daniel Schulz
 *        
 */
class RequestAsset extends AssetImplementation
{

    protected function loadResult(FarahUrl $url): ResultInterface
    {
        $request = new HTTPRequest();
        $request->init($_SERVER);
        foreach ($url->getArguments() as $name => $value) {
            $request->setInputValue($name, $value);
        }
        return ResultCatalog::createDOMWriterResult($url, $request);
    }
}

