<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Node\Asset;

use Slothsoft\Farah\Module\FarahUrl\FarahUrl;
use Slothsoft\Farah\Module\Results\ResultCatalog;
use Slothsoft\Farah\Module\Results\ResultInterface;
use Closure;

/**
 *
 * @author Daniel Schulz
 *        
 */
class ClosureAsset extends AssetImplementation
{

    private $closure;

    public function setClosure(Closure $closure)
    {
        $this->closure = $closure;
    }

    protected function loadResult(FarahUrl $url): ResultInterface
    {
        return ResultCatalog::createFromMixed($url, $this->runClosure($url));
    }

    private function runClosure(FarahUrl $url)
    {
        return $this->closure === null ? null : $this->closure($url);
    }
}

