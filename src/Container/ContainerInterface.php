<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Container;

interface ContainerInterface {
    
    public function put($id, $entry): void;
    
    public function get($id): object;
    
    public function has($id): bool;
}

