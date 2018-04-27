<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Node\Asset;

use Slothsoft\Farah\Module\Executables\ExecutableInterface;
use Slothsoft\Farah\Module\FarahUrl\FarahUrl;
use Slothsoft\Farah\Module\FarahUrl\FarahUrlArguments;
use Closure;

/**
 *
 * @author Daniel Schulz
 *        
 */
class ClosureAsset extends AssetBase
{

    private $closure;

    public function setClosure(Closure $closure)
    {
        $this->closure = $closure;
    }

    protected function loadExecutable(FarahUrlArguments $args): ExecutableInterface
    {
        return $this->runClosure($args);
    }

    private function runClosure(FarahUrl $url)
    {
        return $this->closure === null ? null : ($this->closure)($url);
    }
}

