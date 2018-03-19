<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\RequestProcessor;

use Slothsoft\Farah\Module\FarahUrl\FarahUrl;
use Slothsoft\Farah\Module\FarahUrl\FarahUrlArguments;
use Slothsoft\Farah\Module\FarahUrl\FarahUrlAuthority;
use Slothsoft\Farah\Module\FarahUrl\FarahUrlResolver;
use Slothsoft\Farah\Module\Results\ResultInterface;

class AssetRequestProcessor extends RequestProcessorImplementation
{

    protected function loadResult(): ResultInterface
    {
        $ref = $this->request->path;
        $args = $this->request->input;
        
        $url = FarahUrl::createFromReference($ref, FarahUrlAuthority::createFromVendorAndModule($this->getDefaultVendor(), $this->getDefaultModule()), null, FarahUrlArguments::createFromValueList($args));
        // echo "determined asset url {$url}, processing..." . PHP_EOL;
        return FarahUrlResolver::resolveToResult($url);
    }
}

