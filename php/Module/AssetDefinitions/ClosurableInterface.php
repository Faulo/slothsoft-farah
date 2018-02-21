<?php declare(strict_types=1);
namespace Slothsoft\Farah\Module\AssetDefinitions;

use Slothsoft\Farah\HTTPClosure;

/**
 *
 * @author Daniel Schulz
 *        
 */
interface ClosurableInterface
{

    public function getClosure(): HTTPClosure;
}

