<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Tracking;

use Slothsoft\Core\DBMS\Manager as DBMSManager;
use Exception;

class Manager
{

    protected static $dbName = 'tracking';

    protected static $archive = null;

    public static function getArchive()
    {
        if (! self::$archive) {
            $db = DBMSManager::getDatabase(self::$dbName);
            self::$archive = new Archive($db);
        }
        return self::$archive;
    }

    public static function getView()
    {
        $archive = self::getArchive();
        return new View($archive);
    }

    public static function track(array $request)
    {
        try {
            $archive = self::getArchive();
            $archive->insertTemp($request['REQUEST_TIME_FLOAT'] ?? microtime(true), $request);
        } catch (Exception $e) {
            file_put_contents(__FILE__ . '.txt', $e->getMessage() . PHP_EOL, FILE_APPEND);
        }
    }
}