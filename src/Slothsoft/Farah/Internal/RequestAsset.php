<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Internal;

use Slothsoft\Core\Configuration\ConfigurationRequiredException;
use Slothsoft\Farah\Kernel;
use Slothsoft\Farah\Module\Executables\ExecutableCreator;
use Slothsoft\Farah\Module\Executables\ExecutableInterface;
use Slothsoft\Farah\Module\FarahUrl\FarahUrlArguments;
use Slothsoft\Farah\Module\Node\Asset\AssetBase;

/**
 *
 * @author Daniel Schulz
 *        
 */
class RequestAsset extends AssetBase
{

    protected function loadExecutable(FarahUrlArguments $args): ExecutableInterface
    {
        $creator = new ExecutableCreator($this, $args);
        try {
            return $creator->createMessageExecutable(Kernel::getCurrentRequest());
        } catch (ConfigurationRequiredException $e) {
            return $creator->createNullExecutable();
        }
    }
}

