<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Node\Asset;

use Slothsoft\Core\Storage;
use Slothsoft\Core\Calendar\Seconds;
use Slothsoft\Farah\Module\Module;
use Slothsoft\Farah\Module\Executables\ExecutableInterface;
use Slothsoft\Farah\Module\FarahUrl\FarahUrlArguments;
use Slothsoft\Farah\Module\Node\Enhancements\AssetBuilderTrait;
use Slothsoft\Farah\Module\Node\Enhancements\MimeTypeTrait;

/**
 *
 * @author Daniel Schulz
 *        
 */
class ExternalResourceAsset extends AssetBase
{
    use AssetBuilderTrait;
    use MimeTypeTrait;

    public function getSource(): string
    {
        return $this->getElementAttribute(Module::ATTR_SRC);
    }

    private function lookupResource(): AssetInterface
    {
        $contents = (string) Storage::loadExternalFile($this->getSource(), Seconds::DAY);
        return $this->buildResourceFromContents($contents, $this->getName(), $this->getMimeType());
    }

    protected function loadExecutable(FarahUrlArguments $args): ExecutableInterface
    {
        return $this->lookupResource()->lookupExecutable($args);
    }
}

