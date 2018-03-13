<?php
declare(strict_types = 1);
namespace Slothsoft\Farah;

use Slothsoft\Farah\Event\Events\EventInterface;
use Slothsoft\Farah\Event\Events\UseAssetEvent;

/**
 *
 * @author Daniel Schulz
 *        
 */
class LinkedAssetCollector
{

    private $stylesheetList = [];

    private $scriptList = [];

    public function onStylesheet(EventInterface $event)
    {
        if ($event instanceof UseAssetEvent) {
            $this->stylesheetList[$event->getAsset()->getId()] = $event->getAsset();
        }
    }

    public function getStylesheetList()
    {
        return $this->stylesheetList;
    }

    public function onScript(EventInterface $event)
    {
        if ($event instanceof UseAssetEvent) {
            $this->scriptList[$event->getAsset()->getId()] = $event->getAsset();
        }
    }

    public function getScriptList()
    {
        return $this->scriptList;
    }
}

