<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Node\Asset;

use Slothsoft\Core\Storage;
use Slothsoft\Core\Calendar\Seconds;
use Slothsoft\Farah\Module\Module;
use Slothsoft\Farah\Module\FarahUrl\FarahUrl;
use Slothsoft\Farah\Module\Node\Asset\PhysicalAsset\Resource\ResourceInterface;
use Slothsoft\Farah\Module\Node\Enhancements\AssetBuilderTrait;
use Slothsoft\Farah\Module\Node\Enhancements\MimeTypeTrait;
use Slothsoft\Farah\Module\Results\ResultInterface;

/**
 *
 * @author Daniel Schulz
 *        
 */
class ExternalResourceAsset extends AssetImplementation
{
    use AssetBuilderTrait;
    use MimeTypeTrait;

    public function getSource(): string
    {
        return $this->getElementAttribute(Module::ATTR_SRC);
    }

    private function lookupResource(): ResourceInterface
    {
        $contents = Storage::loadExternalFile($this->getSource(), Seconds::DAY);
        return $this->buildResourceFromContents($contents, $this->getName(), $this->getMimeType());
    }

    protected function loadResult(FarahUrl $url): ResultInterface
    {
        return $this->lookupResource()->lookupResultByArguments($url->getArguments());
    }
}

