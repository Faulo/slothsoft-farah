<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Asset\ExecutableBuilderStrategy;

use Slothsoft\Core\IO\FileInfoFactory;
use Slothsoft\Farah\FarahUrl\FarahUrl;
use Slothsoft\Farah\FarahUrl\FarahUrlArguments;
use Slothsoft\Farah\Module\Asset\AssetInterface;
use Slothsoft\Farah\Module\Executable\ExecutableStrategies;
use Slothsoft\Farah\Module\Executable\ResultBuilderStrategy\ResultBuilderStrategyInterface;
use Slothsoft\Farah\Module\Executable\ResultBuilderStrategy\Files\Base64FileResultBuilder;
use Slothsoft\Farah\Module\Executable\ResultBuilderStrategy\Files\HtmlFileResultBuilder;
use Slothsoft\Farah\Module\Executable\ResultBuilderStrategy\Files\TextFileResultBuilder;
use Slothsoft\Farah\Module\Executable\ResultBuilderStrategy\Files\XmlFileResultBuilder;
use Slothsoft\Farah\Module\Manifest\Manifest;
use SplFileInfo;

class FromFilesystemExecutableBuilder implements ExecutableBuilderStrategyInterface {
    
    public function buildExecutableStrategies(AssetInterface $context, FarahUrlArguments $args): ExecutableStrategies {
        $path = $context->getManifestElement()->getAttribute(Manifest::ATTR_REALPATH);
        $type = $context->getManifestElement()->getAttribute(Manifest::ATTR_TYPE, '*/*');
        
        $resultBuilder = $this->createResultBuilderForType($context->createUrl($args), FileInfoFactory::createFromPath($path), $type);
        return new ExecutableStrategies($resultBuilder);
    }
    
    private function createResultBuilderForType(FarahUrl $url, SplFileInfo $file, string $type): ResultBuilderStrategyInterface {
        if ($type === 'application/xml' or substr($type, - 4) === '+xml') {
            return new XmlFileResultBuilder($url, $file);
        }
        if ($type === 'text/html') {
            return new HtmlFileResultBuilder($url, $file);
        }
        if ($type === 'application/javascript' or $type === 'application/json' or substr($type, 0, 5) === 'text/') {
            return new TextFileResultBuilder($url, $file);
        }
        return new Base64FileResultBuilder($url, $file);
    }
}

