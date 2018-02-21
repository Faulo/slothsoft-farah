<?php declare(strict_types=1);
namespace Slothsoft\Farah\Module\AssetDefinitions;

use Slothsoft\Farah\HTTPClosure;

/**
 *
 * @author Daniel Schulz
 *        
 */
class ClosureDefinition extends GenericAssetDefinition implements ClosurableInterface
{

    private $closure;

    public function getClosure(): HTTPClosure
    {
        return $this->closure;
    }

    public function setClosure(HTTPClosure $closure)
    {
        $this->closure = $closure;
    }
}

