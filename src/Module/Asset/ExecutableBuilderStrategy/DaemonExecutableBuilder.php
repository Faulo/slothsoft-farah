<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Asset\ExecutableBuilderStrategy;

use Slothsoft\Farah\Daemon\AbstractDaemonServer;
use Slothsoft\Farah\FarahUrl\FarahUrlArguments;
use Slothsoft\Farah\Module\Asset\AssetInterface;
use Slothsoft\Farah\Module\Executable\ExecutableStrategies;
use Slothsoft\Farah\Module\Executable\ResultBuilderStrategy\ChunkWriterResultBuilder;

class DaemonExecutableBuilder implements ExecutableBuilderStrategyInterface {

    public function buildExecutableStrategies(AssetInterface $context, FarahUrlArguments $args): ExecutableStrategies {
        $name = $context->getManifestElement()->getAttribute('name');
        $server = $context->getManifestElement()->getAttribute('server');
        $port = $context->getManifestElement()->getAttribute('port');
        $writer = new $server($port);
        assert($writer instanceof AbstractDaemonServer);
        return new ExecutableStrategies(new ChunkWriterResultBuilder($writer, "$name.log", false));
    }
}

