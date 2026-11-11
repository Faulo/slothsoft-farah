<?php
declare(strict_types = 1);

namespace Slothsoft\Farah\Tracking;

use Slothsoft\Core\DBMS\Database;
use Slothsoft\Core\DBMS\Manager;
use Slothsoft\Core\DBMS\Table;
use Slothsoft\Farah\HTTPRequest;

/**
 * Legacy request tracking archive built on the slothsoft DBMS layer.
 *
 * @author Daniel Schulz
 * @since 2017-12-28
 * @deprecated Included for historical compatibility only. This API is deprecated and should not be used in new code.
 */
final class Archive {
    
    const PREPARE_OK = 1;
    
    const PREPARE_UPDATE = 2;
    
    const PREPARE_DELETE = 4;
    
    protected Database $db;
    
    protected Table $tempTable;
    
    protected Table $backupTable;
    
    protected string $logTableDefault = '/getPage.php';
    
    private const LOG_TABLE_NAMES = [
        '/getCache.php' => 'log_cache',
        '/getData.php' => 'log_data',
        '/getFragment.php' => 'log_fragment',
        '/getPage.php' => 'log_page',
        '/getResource.php' => 'log_resource',
        '/getScript.php' => 'log_script',
        '/getTemplate.php' => 'log_template',
        'LookupAssetStrategy' => 'lookup_asset',
        'LookupPageStrategy' => 'lookup_page',
        'LookupRouteStrategy' => 'lookup_route'
    ];
    
    /** @var array<string, LogTable> */
    protected array $logTableList = [];
    
    /**
     * @var array{version: int, parseLimit: int, defaultColumn: array<string, mixed>, logColumns: array<string, array<string, mixed>>}
     */
    protected array $config = [
        'version' => 12,
        'parseLimit' => 1000,
        'defaultColumn' => [
            'type' => 'text',
            'size' => null,
            'visible' => false,
            'groupable' => false,
            'searchable' => false
        ],
        'logColumns' => [
            'SERVER_NAME' => [
                'type' => 'tinytext',
                'size' => 32,
                'groupable' => true
            ],
            'REQUEST_TURING' => [
                'type' => 'tinytext',
                'size' => 8,
                'groupable' => true
            ],
            'REQUEST_LANGUAGE' => [
                'type' => 'tinytext',
                'size' => 8,
                'groupable' => true
            ],
            'REQUEST_TIME_DATE' => [
                'type' => 'tinytext',
                'size' => 17,
                'visible' => true,
                'searchable' => true
            ],
            'RESPONSE_TIME' => [
                'type' => 'double',
                'visible' => true
            ],
            'RESPONSE_MEMORY' => [
                'type' => 'float',
                'visible' => true
            ],
            'REMOTE_ADDR' => [
                'type' => 'tinytext',
                'size' => 15,
                'visible' => true,
                'searchable' => true
            ],
            'RESPONSE_STATUS' => [
                'type' => 'smallint',
                'visible' => true,
                // 'groupable' => true,
                'searchable' => true
            ],
            'REQUEST_METHOD' => [
                'type' => 'tinytext',
                'size' => 8,
                'visible' => true,
                // 'groupable' => true,
                'searchable' => true
            ],
            'HTTP_HOST' => [
                'type' => 'tinytext',
                'size' => 32,
                'visible' => true,
                // 'groupable' => true,
                'searchable' => true
            ],
            'REQUEST_URI' => [
                'type' => 'text',
                'size' => 128,
                'visible' => true,
                'searchable' => true
            ],
            'HTTP_USER_AGENT' => [
                'type' => 'text',
                'size' => 128,
                'visible' => true,
                'searchable' => true
            ],
            'HTTP_ACCEPT_LANGUAGE' => [
                'type' => 'text',
                'size' => 32,
                'visible' => true,
                'searchable' => true
            ],
            'RESPONSE_LANGUAGE' => [
                'type' => 'tinytext',
                'size' => 5,
                'visible' => true,
                // 'groupable' => true,
                'searchable' => true
            ],
            'HTTP_ACCEPT' => [
                'type' => 'text',
                'size' => 128,
                'visible' => true,
                'searchable' => true
            ],
            'RESPONSE_TYPE' => [
                'type' => 'tinytext',
                'size' => 32,
                'visible' => true,
                // 'groupable' => true,
                'searchable' => true
            ],
            'HTTP_ACCEPT_ENCODING' => [
                'type' => 'text',
                'size' => 32,
                'visible' => true,
                'searchable' => true
            ],
            'RESPONSE_ENCODING' => [
                'type' => 'tinytext',
                'size' => 8,
                'visible' => true,
                // 'groupable' => true,
                'searchable' => true
            ],
            'HTTP_REFERER' => [
                'type' => 'text',
                'size' => 128,
                'visible' => true,
                'searchable' => true
            ],
            'HTTP_FROM' => [
                'type' => 'text',
                'size' => 128,
                'visible' => true,
                'searchable' => true
            ],
            'RESPONSE_INPUT' => [
                'type' => 'longtext',
                'size' => 128,
                'visible' => true,
                'searchable' => true
            ],
            'HTTP_LAST_EVENT_ID' => [
                'type' => 'tinytext',
                'size' => 8,
                'visible' => true,
                'searchable' => true
            ]
        ]
    ];
    
    public function __construct(Database $db) {
        $this->db = $db;
        
        foreach ($this->config['logColumns'] as &$column) {
            $column = array_merge($this->config['defaultColumn'], $column);
        }
        unset($column);
        
        $this->tempTable = $this->db->getTable('temp');
        $this->backupTable = $this->db->getTable('backup');
        foreach (self::LOG_TABLE_NAMES as $uri => $tableName) {
            $this->logTableList[$uri] = new LogTable($this, $this->db->getTable($tableName));
        }
    }
    
    public function install(): void {
        if (! $this->db->databaseExists()) {
            $this->db->createDatabase();
        }
        
        if (! $this->tempTable->tableExists()) {
            $sqlCols = [
                'id' => 'int NOT NULL AUTO_INCREMENT',
                'time' => 'double NOT NULL',
                'data' => 'longtext NOT NULL',
                'version' => 'int NOT NULL'
            ];
            $sqlKeys = [
                'id',
                'version'
            ];
            $this->tempTable->createTable($sqlCols, $sqlKeys);
        }
        
        foreach ($this->logTableList as $table) {
            $table->install();
        }
    }
    
    /**
     * @return array{version: int, parseLimit: int, defaultColumn: array<string, mixed>, logColumns: array<string, array<string, mixed>>}
     */
    public function getConfig(): array {
        return $this->config;
    }
    
    /**
     * @param array<string, mixed> $data
     */
    public function insertTemp(float $time, array $data, int|string|null $id = null): mixed {
        if ($id === null) {
            $ret = $this->tempTable->insert([
                'time' => $time,
                'data' => json_encode($data)
            ]);
        } else {
            $ret = $this->tempTable->insert([
                'id' => $id,
                'time' => $time,
                'data' => json_encode($data)
            ], [
                'time' => $time,
                'data' => json_encode($data)
            ]);
        }
        return $ret;
    }
    
    /**
     * @param array<mixed> $idList
     */
    public function updateOutdated(array $idList): void {
        $this->tempTable->update([
            'version' => $this->config['version']
        ], $idList);
    }
    
    /**
     * @return array<mixed>
     */
    public function getOutdated(): array {
        return Query::select($this->tempTable, 'id', sprintf('version < %d LIMIT %d', $this->config['version'], $this->config['parseLimit']));
    }
    
    /**
     * @return array<mixed>
     */
    public function getCurrent(): array {
        return Query::select($this->tempTable, 'id', sprintf('version = %d LIMIT %d', $this->config['version'], $this->config['parseLimit']));
    }
    
    /**
     * @return array<string, LogTable>
     */
    public function getLogTableList(): array {
        return $this->logTableList;
    }
    
    public function getLogTableByURI(string $uri): LogTable {
        $ret = $this->logTableList[$this->logTableDefault];
        foreach ($this->logTableList as $test => $table) {
            if (str_starts_with($uri, $test)) {
                $ret = $table;
                break;
            }
        }
        return $ret;
    }
    
    public function getLogTableByStrategy(string $strategy): LogTable {
        return $this->logTableList[$strategy];
    }
    
    protected function fixTemp(): int {
        // fix temp table
        $ret = 0;
        while ($idList = $this->getOutdated()) {
            $ret += count($idList);
            $this->updateOutdated($idList);
        }
        return $ret;
    }
    
    protected function fixIndex(): void {
        foreach ($this->logTableList as $table) {
            $table->fixIndeX();
        }
    }
    
    protected function fixRollback(): bool {
        // rollback temp table to log
        ini_set('memory_limit', '2G');
        foreach ($this->logTableList as $table) {
            echo $table->getName() . PHP_EOL;
            if ($table->fixRollback($this->tempTable)) {
                return true;
            }
        }
        return false;
    }
    
    public function backup(): void {
        $ret = 0;
        while ($idList = $this->getCurrent()) {
            foreach (Query::select($this->tempTable, true, [
                'id' => $idList
            ]) as $row) {
                $this->backupTable->insert($row, $row);
                echo $row['id'] . PHP_EOL;
                $ret++;
            }
            $this->tempTable->delete($idList);
        }
        if ($ret > 0) {
            die("...done! $ret entries backupped.");
        }
    }
    
    public function parse(): int {
        // my_dump($this->fixRollback()); die;
        // $this->fixTemp();
        // $this->fixIndex();
        // return;
        $ret = 0;
        while ($idList = $this->getOutdated()) {
            $rowList = Query::select($this->tempTable, [
                'id',
                'data'
            ], [
                'id' => $idList
            ]);
            foreach ($rowList as $row) {
                $id = $row['id'];
                $data = json_decode($row['data'], true);
                if (is_array($data)) {
                    $status = $this->prepareData($data);
                    $table = isset($data['RESPONSE_STRATEGY']) ? $this->getLogTableByStrategy($data['RESPONSE_STRATEGY']) : $this->getLogTableByURI($data['REQUEST_URI']);
                    
                    switch ($status) {
                        // just insert into log
                        case self::PREPARE_OK:
                            $table->insert($id, $row['data'], $data);
                            break;
                        // update temp, insert into log
                        case self::PREPARE_UPDATE:
                            $row['data'] = json_encode($data);
                            $this->tempTable->update([
                                'data' => $row['data']
                            ], $id);
                            $table->insert($id, $row['data'], $data);
                            break;
                        // delete
                        case self::PREPARE_DELETE:
                            $this->tempTable->delete($id);
                            $table->delete($id);
                            break;
                    }
                    // echo $row['id'] . PHP_EOL;
                }
            }
            $ret += count($idList);
            $this->updateOutdated($idList);
        }
        return $ret;
    }
    
    /**
     * @param array<mixed> $data
     */
    protected function prepareData(array &$data): int {
        $ret = self::PREPARE_OK;
        
        // preset null
        foreach (array_keys($this->config['logColumns']) as $key) {
            if (! isset($data[$key])) {
                $data[$key] = null;
            }
        }
        
        // HTTPRequest
        $backup = json_encode($data);
        HTTPRequest::prepareEnvironment($data);
        if ($backup !== json_encode($data)) {
            $ret = self::PREPARE_UPDATE;
        }
        
        /*
         * if ($data['REQUEST_TURING'] === 'shell' and $data['REQUEST_URI'][0] === 'D') {
         * $data['REQUEST_URI'] = sprintf('/%s%s', basename($data['REQUEST_URI']), $data['PATH_INFO']);
         * $ret = self::PREPARE_UPDATE;
         * }
         * //
         */
        
        /*
         * $status = (int) $data['RESPONSE_STATUS'];
         * if ($data['RESPONSE_STATUS'] !== $status) {
         * $data['RESPONSE_STATUS'] = $status;
         * $ret = self::PREPARE_UPDATE;
         * }
         *
         * $table = $this->getLogTableByURI($data['REQUEST_URI']);
         * if ($table->getName() === 'log_cache') {
         * $ret = self::PREPARE_DELETE;
         * }
         * //
         */
        
        return $ret;
    }
    
    public function import(): int {
        $ret = 0;
        $step = 1000;
        $dbName = 'cms';
        $tableName = 'access_log';
        $dbmsTable = Manager::getTable($dbName, $tableName);
        
        for ($i = 0; $rowList = Query::select($dbmsTable, true, sprintf('1 LIMIT %d, %d', $i, $step)); $i += $step) {
            foreach ($rowList as $row) {
                $id = (int) array_shift($row);
                $time = (float) $row['REQUEST_TIME_FLOAT'];
                $data = array_filter($row, function ($val) {
                    return $val !== null;
                });
                if ($this->insertTemp($time, $data, $id)) {
                    $ret++;
                }
            }
        }
        return $ret;
    }
}
