<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Tracking;

use Slothsoft\Core\DBMS\Table;

class LogTable {
    
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
    
    protected $columnConfig;
    
    public function __construct(Archive $archive, Table $dbmsTable) {
        $this->ownerArchive = $archive;
        $this->dbmsTable = $dbmsTable;
        $config = $this->ownerArchive->getConfig();
        $this->columnConfig = $config['logColumns'];
    }
    
    public function install() {
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
    
    public function getName() {
        return $this->dbmsTable->getName();
    }
    
    public function insert($id, $json, array $data) {
        $sql = [];
        $sql['id'] = $id;
        $sql['data'] = $json;
        foreach (array_keys($this->columnConfig) as $key) {
            $sql[$key] = (isset($data[$key]) and strlen($data[$key])) ? $data[$key] : null;
        }
        return $this->dbmsTable->insert($sql, $sql);
    }
    
    public function delete($id) {
        return $this->dbmsTable->delete($id);
    }
    
    public function selectGroup($column, array $filter, $sql) {
        if (isset($filter[$column])) {
            unset($filter[$column]);
        }
        return $this->dbmsTable->select(sprintf('DISTINCT %s', $column), $filter, sprintf('%s ORDER BY %s', $sql, $column));
    }
    
    public function selectCount(array $filter, $sql) {
        $res = $this->dbmsTable->select('COUNT(*)', $filter, $sql);
        return reset($res);
    }
    
    public function select(array $filter, $sql, $page, $limit) {
        return $this->dbmsTable->select(array_keys($this->columnConfig), $filter, sprintf('%s ORDER BY id DESC LIMIT %d, %d', $sql, $page * $limit, $limit));
    }
    
    public function fixIndex() {
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
    
    public function fixRollback(Table $tempTable) {
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