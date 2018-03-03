<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module;

use Slothsoft\Farah\Event\EventTargetInterface;
use Slothsoft\Farah\Event\EventTargetTrait;
use Slothsoft\Farah\Module\Node\Asset\FragmentAsset;

/**
 *
 * @author Daniel Schulz
 *        
 */
class FragmentProcessor implements EventTargetInterface
{
    use EventTargetTrait;

    private $context;

    public function __construct(FragmentAsset $context)
    {
        $this->context = $context;
    }

    public function process()
    {
        foreach ($this->context->getChildren() as $childAsset) {
            $childAsset->crawlAndFireAppropriateEvents($this);
        }
    }
}

