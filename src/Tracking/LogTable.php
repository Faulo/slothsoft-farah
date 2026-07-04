<?php
declare(strict_types = 1);

namespace Slothsoft\Farah\Tracking;

use Slothsoft\Core\DBMS\Table;

/**
 * Legacy tracking log table view over archived request data.
 *
 * @author Daniel Schulz
 * @since 2017-12-28
 * @deprecated Included for historical compatibility only. This API is deprecated and should not be used in new code.
 */
final class LogTable {
    
    /*
     * protected $colList = [
     * 'REQUEST_TIME_DATE', 'RESPONSE_TIME', 'RESPONSE_MEMORY', 'REMOTE_ADDR', 'RESPONSE_STATUS',
     * 'REQUEST_METHOD', 'HTTP_HOST', 'REQUEST_URI', 'HTTP_USER_AGENT',
     * 'HTTP_ACCEPT_LANGUAGE', 'RESPONSE_LANGUAGE', 'HTTP_ACCEPT', 'RESPONSE_TYPE',
     * 'HTTP_ACCEPT_ENCODING', 'RESPONSE_ENCODING', 'HTTP_REFERER', 'HTTP_FROM', 'RESPONSE_INPUT',
     * ];
     *
     * protected $colList = [
     * 'id',
     * 'request_time_date',
     * 'response_status',
     * 'response_time',
     * 'response_memory',
     * 'remote_address',
     * 'url_scheme',
     * 'url_host',
     * 'url_path',
     * 'url_query',
     * 'url_fragment',
     * ];
     * //
     */
    protected $archive;
    
    protected $dbmsTable;
    
    /**
     * @var array<string, array<string, mixed>>
     */
    protected array $columnConfig;
    
    public function __construct(Archive $archive, Table $dbmsTable) {
        $this->archive = $archive;
        $this->dbmsTable = $dbmsTable;
        $config = $this->archive->getConfig();
        $columnConfig = $config['logColumns'];
        assert(is_array($columnConfig));
        /** @var array<string, array<string, mixed>> $columnConfig */
        $this->columnConfig = $columnConfig;
    }
    
    public function install(): void {
        if (! $this->dbmsTable->tableExists()) {
            $sqlCols = [];
            $sqlCols['id'] = 'int NOT NULL AUTO_INCREMENT';
            $sqlCols['data'] = 'longtext NOT NULL';
            $sqlKeys = [];
            $sqlKeys[] = 'id';
            foreach ($this->columnConfig as $key => $column) {
                $sqlCols[$key] = $column['type'] . ' NULL';
                $index = [];
                $index['name'] = $key;
                $index['columns'] = [];
                $index['columns'][] = isset($column['size']) ? sprintf('`%s`(%s)', $key, $column['size']) : sprintf('`%s`', $key);
                $sqlKeys[] = $index;
            }
            $this->dbmsTable->createTable($sqlCols, $sqlKeys);
        }
    }
    
    public function getName(): string {
        return $this->dbmsTable->getName();
    }
    
    public function insert($id, $json, array $data): mixed {
        $sql = [];
        $sql['id'] = $id;
        $sql['data'] = $json;
        foreach (array_keys($this->columnConfig) as $key) {
            $sql[$key] = (isset($data[$key]) and strlen($data[$key])) ? $data[$key] : null;
        }
        return $this->dbmsTable->insert($sql, $sql);
    }
    
    public function delete($id): mixed {
        return $this->dbmsTable->delete($id);
    }
    
    public function selectGroup($column, array $filter, $sql): array {
        if (isset($filter[$column])) {
            unset($filter[$column]);
        }
        return $this->dbmsTable->select(sprintf('DISTINCT %s', $column), $filter, sprintf('%s ORDER BY %s', $sql, $column));
    }
    
    public function selectCount(array $filter, $sql): mixed {
        $res = $this->dbmsTable->select('COUNT(*)', $filter, $sql);
        return reset($res);
    }
    
    public function select(array $filter, $sql, $page, $limit): array {
        return $this->dbmsTable->select(array_keys($this->columnConfig), $filter, sprintf('%s ORDER BY id DESC LIMIT %d, %d', $sql, $page * $limit, $limit));
    }
    
    public function fixIndex(): void {
        foreach ($this->columnConfig as $key => $column) {
            if ($column['searchable'] or $column['groupable']) {
                $index = [];
                $index['name'] = $key;
                $index['columns'] = [];
                $index['columns'][] = isset($column['size']) ? sprintf('%s(%s)', $key, $column['size']) : $key;
                $this->dbmsTable->addIndex($index);
            }
        }
    }
    
    public function fixRollback(Table $tempTable): int {
        $ret = 0;
        $rowList = $this->dbmsTable->select();
        foreach ($rowList as $row) {
            $sql = [
                'data' => $row['data'],
                'version' => 0
            ];
            $ret += $tempTable->update($sql, $row['id']);
        }
        return $ret;
    }
}
