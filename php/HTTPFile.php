<?php
/***********************************************************************
 * Slothsoft\Farah\HTTPFile v1.00 28.05.2014 � Daniel Schulz
 * 
 * 	Changelog:
 *		v1.00 28.05.2014
 *			initial release
 ***********************************************************************/
namespace Slothsoft\Farah;

use Slothsoft\Core\DOMHelper;
use Slothsoft\Core\Storage;
use DOMDocument;
declare(ticks = 1000);

class HTTPFile
{

    const CURL_ENABLED = true;

    const CURL_COMMAND = 'curl %s --output %s --header %s --connect-timeout 300 --retry 3 --http1.1 --silent --fail --insecure --location';

    /**
     * @return string
     */
    public static function getTempFile()
    {
        // $ret = tempnam(sys_get_temp_dir() . DIRECTORY_SEPARATOR . __NAMESPACE__, __CLASS__);
        $ret = temp_file(__CLASS__);
        // my_dump($ret);
        return $ret;
    }

    /**
     * @param string $filePath
     * @param string $fileName
     * @return \Slothsoft\Farah\HTTPFile
     */
    public static function createFromPath(string $filePath, string $fileName = '')
    {
        return new HTTPFile($filePath, $fileName);
    }

    /**
     * @param DOMDocument $doc
     * @param string $fileName
     * @return \Slothsoft\Farah\HTTPFile
     */
    public static function createFromDocument(DOMDocument $doc, string $fileName = '')
    {
        $fileName = (string) $fileName;
        if ($fileName === '') {
            $fileName = 'index.xml';
        }
        $filePath = self::getTempFile();
        $doc->save($filePath);
        return self::createFromPath($filePath, $fileName);
    }

    /**
     * @param string $content
     * @param string $fileName
     * @return \Slothsoft\Farah\HTTPFile
     */
    public static function createFromString(string $content, string $fileName = '')
    {
        $fileName = (string) $fileName;
        if ($fileName === '') {
            $fileName = 'index.txt';
        }
        $filePath = self::getTempFile();
        file_put_contents($filePath, $content);
        return self::createFromPath($filePath, $fileName);
    }

    /**
     * @param resource $resource
     * @param string $fileName
     * @return \Slothsoft\Farah\HTTPFile
     */
    public static function createFromStream($resource, string $fileName = '')
    {
        $fileName = (string) $fileName;
        if ($fileName === '') {
            $fileName = 'index.txt';
        }
        $filePath = self::getTempFile();
        file_put_contents($filePath, $resource);
        return self::createFromPath($filePath, $fileName);
    }
    
    /**
     * @param mixed $object
     * @param string $fileName
     * @return \Slothsoft\Farah\HTTPFile
     */
    public static function createFromJSON($object, string $fileName = '')
    {
        $fileName = (string) $fileName;
        if ($fileName === '') {
            $fileName = 'data.json';
        }
        return self::createFromString(json_encode($object), $fileName);
    }

    /**
     * @param string $phpCommand
     * @param string $fileName
     * @return NULL|\Slothsoft\Farah\HTTPFile
     */
    public static function createFromPHP(string $phpCommand, string $fileName = '')
    {
        $fileName = (string) $fileName;
        if ($fileName === '') {
            $fileName = basename($phpCommand);
            if ($fileName === '') {
                $fileName = 'data.bin';
            }
        }
        $filePath = self::getTempFile();
        $exec = sprintf('php %s > %s', $phpCommand, $filePath);
        exec($exec);
        
        return file_exists($filePath) ? self::createFromPath($filePath, $fileName) : null;
    }

    /**
     * @param string $url
     * @param string $fileName
     * @return NULL|\Slothsoft\Farah\HTTPFile
     */
    public static function createFromURL(string $url, string $fileName = '')
    {
        $fileName = (string) $fileName;
        if ($fileName === '') {
            $fileName = basename($url);
            if ($fileName === '') {
                $fileName = 'data.bin';
            }
        }
        $param = parse_url($url);
        if (! isset($param['host'])) {
            $url = 'http://slothsoft.net' . $url;
        }
        
        if (self::CURL_ENABLED) {
            $refererURI = sprintf('Referer: %s://%s%s', $param['scheme'], $param['host'], $param['path']);
            $filePath = self::getTempFile();
            $downloadExec = sprintf(self::CURL_COMMAND, escapeshellarg(urldecode($url)), escapeshellarg($filePath), escapeshellarg($refererURI));
            exec($downloadExec);
            // file_put_contents(__FILE__ . '.txt', $downloadExec . PHP_EOL, FILE_APPEND);
            $ret = file_exists($filePath) ? self::createFromPath($filePath, $fileName) : null;
        } else {
            @$data = file_get_contents($url);
            $ret = strlen($data) ? self::createFromString($data, $fileName) : null;
        }
        return $ret;
    }

    /**
     * @param string $filePath
     * @param string $url
     * @param int $headerCache
     * @return NULL|\Slothsoft\Farah\HTTPFile
     */
    public static function createFromDownload(string $filePath, string $url, int $headerCache = TIME_YEAR)
    {
        $ret = self::verifyDownload($filePath, $url, $headerCache);
        if (! $ret) {
            if ($file = self::createFromURL($url)) {
                $ret = $file->copyTo(dirname($filePath), basename($filePath));
            }
        }
        return $ret ? self::createFromPath($filePath) : null;
    }

    /**
     * @param string $url
     * @param int $headerCache
     * @return boolean
     */
    public static function verifyURL(string $url, int $headerCache = TIME_YEAR)
    {
        $ret = false;
        if ($res = Storage::loadExternalHeader($url, $headerCache)) {
            $status = isset($res['status']) ? (int) $res['status'] : HTTPResponse::STATUS_BAD_REQUEST;
            if ($status < HTTPResponse::STATUS_BAD_REQUEST) {
                $length = isset($res['content-length']) ? (string) $res['content-length'] : '0';
                if ($length !== '0') {
                    $ret = true;
                }
            }
        }
        return $ret;
    }

    /**
     * @param string $filePath
     * @param string $url
     * @param int $headerCache
     * @return boolean
     */
    public static function verifyDownload(string $filePath, string $url, int $headerCache = TIME_YEAR)
    {
        $ret = false;
        if (file_exists($filePath)) {
            if ($headerCache === - 1) {
                $ret = true;
            } else {
                $res = Storage::loadExternalHeader($url, $headerCache);
                $sizeA = isset($res['content-length']) ? (string) $res['content-length'] : '0';
                $sizeB = (string) filesize($filePath);
                if ($sizeA === $sizeB) {
                    $ret = true;
                }
            }
        }
        return $ret;
    }

    protected $path;

    protected $name;

    protected function __construct($filePath, $fileName = null)
    {
        if ($fileName === null) {
            $fileName = basename($filePath);
        }
        $this->path = $filePath;
        $this->name = $fileName;
    }

    public function getPath()
    {
        return $this->path;
    }

    public function getName()
    {
        return $this->name;
    }
    
    public function getContents()
    {
        return file_get_contents($this->getPath());
    }
    public function setContents($content)
    {
        return file_put_contents($this->getPath(), $content);
    }
    
    public function getDocument()
    {
        return DOMHelper::loadDocument($this->getPath());
    }
    public function setDocument(DOMDocument $content)
    {
        return $content->save($this->getPath());
    }

    public function copyTo($dir, $name = null, $copyClosure = null)
    {
        $ret = false;
        if ($dir = realpath($dir)) {
            if (! $name) {
                $name = $this->name;
            }
            $sourcePath = $this->path;
            $targetPath = $dir . DIRECTORY_SEPARATOR . $name;
            if (is_callable($copyClosure)) {
                $ret = $copyClosure($sourcePath, $targetPath);
            } elseif (is_string($copyClosure) and strlen($copyClosure)) {
                $command = sprintf($copyClosure, escapeshellarg($sourcePath), escapeshellarg($targetPath));
                exec($command, $output, $result);
                $ret = ($result === 0);
            } else {
                $ret = copy($sourcePath, $targetPath);
            }
        }
        return $ret;
    }

    public function delete()
    {
        return unlink($this->getPath());
    }
}