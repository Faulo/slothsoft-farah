<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Exception;

use Slothsoft\Farah\Module\Node\Asset\AssetInterface;

class EmptyTransformationException extends \RuntimeException
{

    public function __construct(AssetInterface $template)
    {
        parent::__construct("Transformation template {$template->getId()} resulted in an empty document!");
    }
}
