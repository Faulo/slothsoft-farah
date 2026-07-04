<?php
declare(strict_types = 1);

namespace Slothsoft\Farah\Tracking;

use Exception;
use Slothsoft\Core\DBMS\Manager as DBMSManager;
use Slothsoft\Core\ServerEnvironment;

/**
 * Legacy facade for recording Farah request tracking data.
 *
 * @author Daniel Schulz
 * @since 2017-12-28
 * @deprecated Included for historical compatibility only. This API is deprecated and should not be used in new code.
 */
final class Manager {
    
    protected static $dbName = 'tracking';
    
    protected static $archive = null;
    
    public static function getArchive(): Archive {
        if (! self::$archive) {
            $db = DBMSManager::getDatabase(self::$dbName);
            self::$archive = new Archive($db);
        }
        return self::$archive;
    }
    
    public static function getView(): View {
        $archive = self::getArchive();
        return new View($archive);
    }
    
    public static function track(array $request): void {
        try {
            $archive = self::getArchive();
            $archive->insertTemp($request['REQUEST_TIME_FLOAT'] ?? microtime(true), $request);
        } catch (Exception $e) {
            file_put_contents(ServerEnvironment::getLogDirectory() . DIRECTORY_SEPARATOR . 'track.log', $e->getMessage());
        }
    }
}
