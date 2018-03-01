<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Node\Asset;

use Slothsoft\Farah\Event\EventTargetInterface;
use Slothsoft\Farah\Event\Events\UseAssetEvent;
use Slothsoft\Farah\Module\Module;
use Slothsoft\Farah\Module\FarahUrl\FarahUrl;
use Slothsoft\Farah\Module\Results\FragmentResult;
use Slothsoft\Farah\Module\Results\ResultInterface;

/**
 *
 * @author Daniel Schulz
 *        
 */
class FragmentAsset extends ContainerAsset
{
    protected function loadResult(FarahUrl $url): ResultInterface
    {
        return new FragmentResult($url, $this);
    }
    public function crawlAndFireAppropriateEvents(EventTargetInterface $listener) {
        $event = new UseAssetEvent();
        $event->initEvent(Module::EVENT_USE_DOCUMENT, [
            'asset' => $this,
        ]);
        $listener->dispatchEvent($event);
    }
}

