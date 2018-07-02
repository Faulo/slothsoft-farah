<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Internal;

use Slothsoft\Core\IO\AdapterDelegates\FileWriterFromStringDelegate;
use Slothsoft\Farah\FarahUrl\FarahUrlArguments;
use Slothsoft\Farah\Module\Asset\AssetInterface;
use Slothsoft\Farah\Module\Asset\ExecutableBuilderStrategy\ExecutableBuilderStrategyInterface;
use Slothsoft\Farah\Module\Executable\ExecutableStrategies;
use Slothsoft\Farah\Module\Executable\ResultBuilderStrategy\FileWriterResultBuilder;

/**
 *
 * @author Daniel Schulz
 *        
 */
class PhpinfoBuilder implements ExecutableBuilderStrategyInterface
{

    public function buildExecutableStrategies(AssetInterface $context, FarahUrlArguments $args): ExecutableStrategies
    {
        $delegate = function (): string {
            ob_start();
            phpinfo();
            $data = ob_get_contents();
            ob_clean();
            return $data;
        };
        $writer = new FileWriterFromStringDelegate($delegate, 'phpinfo.html');
        $resultBuilder = new FileWriterResultBuilder($writer);
        return new ExecutableStrategies($resultBuilder);
    }
}

