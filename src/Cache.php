<?php
declare(strict_types = 1);
/**
 * *********************************************************************
 * Slothsoft\Farah\Cache v1.00 19.10.2012 © Daniel Schulz
 *
 * Changelog:
 * v1.00 19.10.2012
 * initial release
 * *********************************************************************
 */
namespace Slothsoft\Farah;

use Slothsoft\Core\FileSystem;
use Slothsoft\Core\ServerEnvironment;

class Cache
{

    protected $rootDir;

    protected $loadScript = '/getCache.php/';

    public function __construct()
    {
        $this->rootDir = ServerEnvironment::getCacheDirectory();
    }

    public function getPath($uri, $cacheDir = '')
    {
        $path = $this->sanitizeName($uri);
        $ret = $this->rootDir . $cacheDir;
        if (! is_dir($ret)) {
            mkdir($ret, 0777, true);
        }
        return realpath($ret) . DIRECTORY_SEPARATOR . $path;
    }

    public function getURI($uri, $cacheDir = '')
    {
        $ret = $this->getPath($uri, $cacheDir);
        $ret = substr($ret, strlen($this->rootDir));
        $ret = str_replace('\\', '/', $ret);
        $ret = $this->loadScript . $ret;
        return $ret;
    }

    public function getFile($path)
    {
        $ret = null;
        if (file_exists($this->rootDir . $path)) {
            $ret = $this->rootDir . $path;
        }
        return $ret;
    }

    public function mergeFiles(array $fileList, $targetDir = '', $zipFunction = null)
    {
        if (! count($fileList)) {
            return;
        }
        
        $timeList = [];
        foreach ($fileList as $file) {
            $timeList[$file] = FileSystem::changetime($file);
        }
        
        $ext = explode('.', current($fileList));
        $ext = end($ext);
        $cacheName = sprintf('%s.%s.%s', $this->createName($fileList), max($timeList), $ext);
        $cacheURI = $this->getURI($cacheName, $targetDir);
        $cachePath = $this->getPath($cacheName, $targetDir);
        if (is_file($cachePath)) {
            $checkTime = FileSystem::changetime($cachePath);
            $renew = false;
            foreach ($fileList as $file) {
                if ($timeList[$file] > $checkTime) {
                    $renew = true;
                    break;
                }
            }
        } else {
            $renew = true;
        }
        if ($renew) {
            $content = '';
            foreach ($fileList as $file) {
                $content .= file_get_contents($file) . PHP_EOL;
            }
            if ($zipFunction) {
                $content = $zipFunction($content);
            }
            file_put_contents($cachePath, $content);
        }
        return $cacheURI;
    }

    protected function createName(array $names)
    {
        return md5(implode(PHP_EOL, $names));
    }

    protected function sanitizeName($path)
    {
        return trim(str_replace([
            '/',
            '\\',
            ' ',
            ':',
            '"',
            "'",
            '&',
            '?',
            '<',
            '>',
            '*',
            '|'
        ], [
            '_',
            '_'
        ], $path));
    }
}