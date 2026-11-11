<?php
declare(strict_types = 1);

namespace Slothsoft\Farah\Container;

interface ContainerInterface {
    
    public function put(mixed $id, object $entry): void;
    
    public function get(mixed $id): object;
    
    public function has(mixed $id): bool;
}

