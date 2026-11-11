<?php
declare(strict_types = 1);

namespace Slothsoft\Farah\Tracking;

use RuntimeException;
use Slothsoft\Core\DBMS\Table;

/**
 * @internal
 */
final class Query {
    
    /**
     * @param array<mixed>|string|bool $columns
     * @param array<mixed>|string $filter
     * @return array<mixed>
     * @throws RuntimeException When the tracking query fails.
     */
    public static function select(Table $table, array|string|bool $columns = true, array|string $filter = '', string $suffix = ''): array {
        $result = $table->select($columns, $filter, $suffix);
        if ($result === null) {
            throw new RuntimeException(sprintf('Failed to read tracking table "%s".', $table->getName()));
        }
        return $result;
    }
}
