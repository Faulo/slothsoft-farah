<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Internal;

use Slothsoft\Core\IO\Writable\Delegates\StringWriterFromStringDelegate;
use Slothsoft\Farah\FarahUrl\FarahUrlArguments;
use Slothsoft\Farah\Module\Asset\AssetInterface;
use Slothsoft\Farah\Module\Asset\ExecutableBuilderStrategy\ExecutableBuilderStrategyInterface;
use Slothsoft\Farah\Module\Executable\ExecutableStrategies;
use Slothsoft\Farah\Module\Executable\ResultBuilderStrategy\FileWriterResultBuilder;
use Slothsoft\Core\IO\Writable\Adapter\FileWriterFromStringWriter;

/**
 *
 * @author Daniel Schulz
 *        
 */
class PhpinfoBuilder implements ExecutableBuilderStrategyInterface {

    public function buildExecutableStrategies(AssetInterface $context, FarahUrlArguments $args): ExecutableStrategies {
        $delegate = function (): string {
            ob_start();
            phpinfo();
            $data = ob_get_contents();
            ob_clean();
            if (PHP_SAPI === 'cli') {
                $data = '<pre>' . htmlentities($data, ENT_XML1 | ENT_DISALLOWED, 'UTF-8') . '</pre>';
            }
            return $data;
        };
        $writer = new StringWriterFromStringDelegate($delegate);
        $writer = new FileWriterFromStringWriter($writer);
        $resultBuilder = new FileWriterResultBuilder($writer, 'phpinfo.xhtml');
        return new ExecutableStrategies($resultBuilder);
    }
}

