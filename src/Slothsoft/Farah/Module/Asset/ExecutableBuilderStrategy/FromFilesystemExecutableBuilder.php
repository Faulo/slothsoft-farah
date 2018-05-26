<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Asset\ExecutableBuilderStrategy;

use Slothsoft\Core\IO\HTTPFile;
use Slothsoft\Farah\FarahUrl\FarahUrlArguments;
use Slothsoft\Farah\Module\Asset\AssetInterface;
use Slothsoft\Farah\Module\Executable\ExecutableStrategies;
use Slothsoft\Farah\Module\Executable\ResultBuilderStrategy\FileResultBuilder;

class FromFilesystemExecutableBuilder implements ExecutableBuilderStrategyInterface
{

    public function buildExecutableStrategies(AssetInterface $context, FarahUrlArguments $args): ExecutableStrategies
    {
        $path = $context->getManifestElement()->getAttribute('realpath');
        $resultBuilder = new FileResultBuilder(HTTPFile::createFromPath($path));
        return new ExecutableStrategies($resultBuilder);
    }
}

