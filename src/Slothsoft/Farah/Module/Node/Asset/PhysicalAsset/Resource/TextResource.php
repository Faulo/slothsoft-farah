<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Node\Asset\PhysicalAsset\Resource;

use Slothsoft\Farah\Module\FarahUrl\FarahUrl;
use Slothsoft\Farah\Module\Results\ResultInterface;
use Slothsoft\Farah\Module\Results\TextFileResult;

/**
 *
 * @author Daniel Schulz
 *        
 */
class TextResource extends ResourceImplementation
{
    protected function loadResult(FarahUrl $url): ResultInterface
    {
        return new TextFileResult($url, $this->getRealPath());
    }
}

