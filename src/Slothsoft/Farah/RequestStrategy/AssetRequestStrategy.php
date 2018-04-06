<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\RequestStrategy;

use Slothsoft\Farah\HTTPResponse;
use Slothsoft\Farah\Exception\AssetPathNotFoundException;
use Slothsoft\Farah\Exception\ModuleNotFoundException;
use Slothsoft\Farah\Module\FarahUrl\FarahUrl;
use Slothsoft\Farah\Module\FarahUrl\FarahUrlArguments;
use Slothsoft\Farah\Module\FarahUrl\FarahUrlAuthority;
use Slothsoft\Farah\Module\FarahUrl\FarahUrlResolver;
use Slothsoft\Farah\Module\Results\ResultInterface;
use Slothsoft\Farah\Exception\HttpStatusException;

class AssetRequestStrategy extends RequestStrategyImplementation
{

    protected function loadResult(): ResultInterface
    {
        $request = $this->getRequest();
        $ref = $request->path;
        $args = $request->input;
        
        $url = FarahUrl::createFromReference($ref, FarahUrlAuthority::createFromVendorAndModule($this->getDefaultVendor(), $this->getDefaultModule()), null, FarahUrlArguments::createFromValueList($args));
        // echo "determined asset url {$url}, processing..." . PHP_EOL;
        try {
            return FarahUrlResolver::resolveToResult($url);
        } catch (ModuleNotFoundException $e) {
            throw new HttpStatusException($e->getMessage(), HTTPResponse::STATUS_NOT_FOUND, $e);
        } catch (AssetPathNotFoundException $e) {
            throw new HttpStatusException($e->getMessage(), HTTPResponse::STATUS_NOT_FOUND, $e);
        }
    }
}

