<?php declare(strict_types=1);
namespace Slothsoft\Farah\Module\PathResolvers;

use Slothsoft\Farah\Exception\ExceptionContext;
use Slothsoft\Farah\Module\AssetDefinitions\AssetDefinitionInterface;
use OutOfRangeException;

/**
 *
 * @author Daniel Schulz
 *        
 */
class NullPathResolver implements PathResolverInterface
{

    private $definition;

    public function __construct(AssetDefinitionInterface $definition)
    {
        $this->definition = $definition;
    }

    public function resolvePath(string $path): AssetDefinitionInterface
    {
        if ($path === '/') {
            return $this->definition;
        }
        throw ExceptionContext::append(new OutOfRangeException("Cannot traverse from {$this->definition->getId()} to $path."), [
            'definition' => $this->definition
        ]);
    }

    public function getPathMap(): array
    {
        return [
            '/' => $this->definition
        ];
    }
}

