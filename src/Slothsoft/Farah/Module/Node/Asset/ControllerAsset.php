<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Node\Asset;

use Slothsoft\Farah\Event\EventTargetInterface;
use Slothsoft\Farah\Event\Events\UseAssetEvent;
use Slothsoft\Farah\Module\Module;
use Slothsoft\Farah\Module\Controllers\ControllerInterface;
use Slothsoft\Farah\Module\FarahUrl\FarahUrl;
use Slothsoft\Farah\Module\ParameterFilters\ParameterFilterInterface;
use Slothsoft\Farah\Module\PathResolvers\PathResolverInterface;
use Slothsoft\Farah\Module\Results\ResultInterface;

/**
 *
 * @author Daniel Schulz
 *        
 */
class ControllerAsset extends AssetImplementation
{

    private $controller;

    public function getController(): ControllerInterface
    {
        if ($this->controller === null) {
            $this->controller = $this->loadController();
        }
        return $this->controller;
    }

    private function loadController(): ControllerInterface
    {
        $controllerName = $this->getElementAttribute('class');
        $controller = new $controllerName($this);
        assert($controller instanceof ControllerInterface, "<sfm:call-controller> class $controllerName must implement ControllerInterface!");
        $controller->setAsset($this);
        return $controller;
    }

    protected function loadParameterFilter(): ParameterFilterInterface
    {
        return $this->getController()->createParameterFilter();
    }

    protected function loadPathResolver(): PathResolverInterface
    {
        return $this->getController()->createPathResolver();
    }

    protected function loadResult(FarahUrl $url): ResultInterface
    {
        return $this->getController()->createResult($url);
    }

    public function crawlAndFireAppropriateEvents(EventTargetInterface $listener)
    {
        $event = new UseAssetEvent();
        $event->initEvent(Module::EVENT_USE_DOCUMENT, [
            'asset' => $this
        ]);
        $listener->dispatchEvent($event);
    }
}

