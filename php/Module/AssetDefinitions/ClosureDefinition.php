<?php
namespace Slothsoft\Farah\Module\AssetDefinitions;

use Slothsoft\Farah\HTTPClosure;

/**
 *
 * @author Daniel Schulz
 *        
 */
class ClosureDefinition extends AssetDefinition implements ClosurableDefinition
{
    private $closure;
    public function getClosure() : HTTPClosure {
        return $this->closure;
    }
    public function setClosure(HTTPClosure $closure) {
        $this->closure = $closure;
    }
}

