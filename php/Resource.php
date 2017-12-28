<?php
/***********************************************************************
 * Slothsoft\Farah\Resource v1.02 05.01.2015 © Daniel Schulz
 * 
 * 	Changelog:
 *		v1.02 05.01.2015
 *			loadMode['thumbnail']
 *		v1.01 11.07.2014
 *			loadMode['document']
 *		v1.00 19.10.2012
 *			initial release
 ***********************************************************************/
namespace Slothsoft\Farah;

use Slothsoft\Core\FileSystem;
use DOMXPath;
use DOMDocument;
use DOMElement;
use Exception;
use RuntimeException;

class Resource
{

    const CACHE_ACTIVE = false;

    const FILE_MIME = 'mod/core/res/mimeTypes.xml';

    // protected static $mimeDoc; //DOMDocument
    // protected static $mimePath; //DOMXPath
    protected static $loadedMimes = [];
 // DOMElement[]
    protected static $cachePath;

    const ERROR_FILE_NOTFOUND = 'Could not locate resource "%1$s" at "%2$s"';

    const ERROR_MIME_NOTFOUND = 'Mime Type not supported: "%1$s"';

    /*
     * public static function construct() {
     * self::$cachePath = 'R:/Temp/CMSResource/';
     * if (!is_dir(self::$cachePath)) {
     * mkdir(self::$cachePath, 0777, true);
     * }
     * }
     * //
     */
    protected static $mimePath = null;

    protected static function getMimePath()
    {
        if (! self::$mimePath) {
            $mimeDoc = new DOMDocument('1.0', 'UTF-8');
            $mimeDoc->load(SERVER_ROOT . self::FILE_MIME);
            self::$mimePath = new DOMXPath($mimeDoc);
        }
        return self::$mimePath;
    }

    public static function hash($path, DOMElement $resNode, $cacheName = false, $suffix = '')
    {
        $ret = $path . $resNode->getAttribute('name') . '.' . $resNode->getAttribute('path');
        if (strlen($suffix)) {
            $ret .= '.' . $suffix;
        }
        
        if ($cacheName and self::$cachePath) {
            $ret = self::$cachePath . FileSystem::filenameEncode($ret) . '.xml';
        }
        
        return $ret;
    }

    public static function getResource($path, DOMElement $resNode, $loadFile = '')
    {
        try {
            $mimeType = $resNode->getAttribute('type');
            if ($ret = self::instantiateResource($mimeType)) {
                $ret = $ret->init($path, $resNode, $loadFile);
            }
        } catch(Exception $e) {
            $ret = null;
        }
        return $ret;
    }

    public static function getResourceDir($path, DOMElement $resNode, $loadFile = '')
    {
        $ret = [];
        $realpath = $resNode->getAttribute('path') . '/';
        if (strpos($realpath, ':') === false) {
            $realpath = $path . $realpath;
        }
        if (is_dir($realpath)) {
            $pattern = $resNode->hasAttribute('pattern') ? '/' . $resNode->getAttribute('pattern') . '/' : false;
            $extension = $resNode->hasAttribute('ext') ? $resNode->getAttribute('ext') : (string) self::getExtension($resNode->getAttribute('type'));
            if (strlen($extension)) {
                $extension = '/\.' . $extension . '$/i';
            } else {
                $extension = false;
            }
            $arr = FileSystem::scanDir($realpath, FileSystem::SCANDIR_EXCLUDE_DIRS | FileSystem::SCANDIR_EXCLUDE_HIDDEN);
            foreach ($arr as $file) {
                if ($pattern === false or preg_match($pattern, $file)) {
                    if ($extension === false or preg_match($extension, $file)) {
                        $node = $resNode->cloneNode(true);
                        $node->setAttribute('path', utf8_encode($file));
                        if ($res = self::getResource($realpath, $node, $loadFile)) {
                            $ret[] = $res;
                        }
                    }
                }
            }
            /*
             * //cache
             * $cacheName = urlencode($path);
             * if (file_exists(self::$cachePath . $cacheName)) {
             * $ret = unserialize(file_get_contents(self::$cachePath . $cacheName));
             * foreach ($ret as $res) {
             * my_dump($res->asDocument());
             * }
             * die();
             * } else {
             * $pattern = $resNode->hasAttribute('pattern')
             * ? $resNode->getAttribute('pattern')
             * : null;
             * $extension = '/^.*\.'.self::getExtension($resNode->getAttribute('type')).'$/i';
             * $arr = scandir($path);
             * foreach ($arr as $file) {
             * if ($file === '.' or $file === '..') {
             * continue;
             * }
             * if ($pattern === null or preg_match('/' . $pattern . '/', $file)) {
             * if (preg_match($extension, $file)) {
             * $node = $resNode->cloneNode(true);
             * $node->setAttribute('path', $file);
             * if ($res = self::getResource($path, $node, $loadFile)) {
             * $ret[] = $res;
             * }
             * }
             * }
             * }
             * file_put_contents(self::$cachePath . $cacheName, serialize($ret));
             * }
             * //
             */
        }
        return $ret;
    }

    protected static function instantiateResource($mimeType)
    {
        if (isset(self::$loadedMimes[$mimeType]) or self::loadMime($mimeType)) {
            switch ($mimeType) {
                case 'text/html':
                    return new ResourceHTML();
                case 'image/svg+xml':
                case 'application/xhtml+xml':
                case 'application/rdf+xml':
                case 'application/xml':
                    return new ResourceXML();
                case 'application/json':
                    return new ResourceJSON();
                case 'application/x-nbt':
                    return new ResourceNBT();
                case 'application/x-mcr':
                    return new ResourceMCRegion();
                case 'application/x-mca':
                    return new ResourceMCAnvil();
                case 'application/x-ttf':
                    return new ResourceFont();
                case 'text/plain':
                    return new ResourceText();
                case 'text/csv':
                    return new ResourceCSV();
                case 'image/*':
                case 'image/png':
                    return new ResourceImage();
                case 'application/octet-stream':
                default:
                    return new Resource();
            }
        }
        return false;
    }

    protected static function loadMime($mimeType)
    {
        $arr = explode('/', $mimeType);
        if (count($arr) === 2) {
            $expr = '/*/type[@name="' . $arr[0] . '"]';
            if ($arr[1] !== '*') {
                $expr .= '/sub[@name="' . $arr[1] . '"]';
            }
            $mimePath = self::getMimePath();
            if ($res = $mimePath->evaluate($expr) and $res->length) {
                self::$loadedMimes[$mimeType] = $res->item(0);
                return true;
            }
        }
        trigger_error(sprintf(self::ERROR_MIME_NOTFOUND, $mimeType), E_USER_WARNING);
        return false;
    }

    public static function getExtension($mimeType)
    {
        if ((isset(self::$loadedMimes[$mimeType]) or self::loadMime($mimeType)) and self::$loadedMimes[$mimeType]->hasAttribute('ext')) {
            return self::$loadedMimes[$mimeType]->getAttribute('ext');
        }
        return false;
    }

    public static function getMime($extension)
    {
        $expr = sprintf('/*/type/sub[@ext="%s"][1]', strtolower($extension));
        $mimePath = self::getMimePath();
        if ($res = $mimePath->evaluate($expr) and $res->length) {
            $expr = 'concat(../@name, "/", @name)';
            return $mimePath->evaluate($expr, $res->item(0));
        }
        return 'application/octet-stream';
    }

    protected $file;

    protected $path;

    protected $realpath;

    protected $cachepath;

    protected $uri;

    protected $resNode;

    protected $resDoc;

    protected $mimeType;

    protected $fileData;

    protected $fileHandle;

    protected $options = [];

    protected $loadMode = [
        'default' => true,
        'status' => false,
        'xml' => false,
        'base64' => false,
        'document' => false,
        'thumbnail' => false
    ];

    protected function __construct()
    {}

    protected function init($path, DOMElement $resNode, $loadFile)
    {
        $this->path = $path;
        $this->mimeType = $resNode->getAttribute('type');
        $this->realpath = utf8_decode($resNode->getAttribute('path')); // .'.'. self::getExtension($this->mimeType);
        if (strpos($this->realpath, ':') === false) {
            $this->realpath = $this->path . $this->realpath;
        }
        $this->realpath = realpath($this->realpath);
        if (! $this->realpath) {
            throw new RuntimeException(sprintf(self::ERROR_FILE_NOTFOUND, $resNode->getAttribute('name'), $this->getPath()));
        }
        $this->file = basename($this->realpath);
        if ($resNode->tagName === HTTPDocument::TAG_RESOURCE) {
            $uri = sprintf('/getResource.php/%s', $resNode->getAttribute('data-cms-path'));
        } else {
            $name = $resNode->getAttribute('path');
            if ($ext = self::getExtension($resNode->getAttribute('type'))) {
                $name = substr($name, 0, strrpos($name, '.'));
            }
            $uri = sprintf('/getResource.php/%s/%s', $resNode->getAttribute('data-cms-path'), rawurlencode($name));
        }
        // $uri = str_replace(' ', '%20', $uri);
        $this->uri = $uri;
        
        $options = self::$loadedMimes[$this->mimeType]->getElementsByTagName('options');
        foreach ($options as $opt) {
            foreach ($opt->attributes as $attr) {
                $this->options[$attr->name] = $resNode->hasAttribute($attr->name) ? $resNode->getAttribute($attr->name) : $attr->value;
            }
        }
        
        $loadMode = explode(' ', $loadFile);
        foreach ($loadMode as $loadMode) {
            if (isset($this->loadMode[$loadMode])) {
                $this->loadMode[$loadMode] = true;
            }
        }
        
        if (! $this->loadCache(self::hash($path, $resNode, true, implode('-', array_keys(array_filter($this->loadMode)))))) {
            $this->resDoc = new DOMDocument();
            $this->resNode = $this->resDoc->importNode($resNode, true);
            $this->resDoc->appendChild($this->resNode);
            
            if ($this->loadMode['thumbnail']) {
                $this->loadFileThumbnail();
            }
            if ($this->loadMode['document']) {
                $this->loadFileDocument();
            }
            if ($this->loadMode['default']) {
                $this->loadFileDefault();
            }
            if ($this->loadMode['status']) {
                $this->loadFileStatus();
            }
            if ($this->loadMode['xml']) {
                $this->loadFileXML();
            }
            if ($this->loadMode['base64']) {
                $this->loadFileBase64();
            }
            
            $this->saveCache();
            // my_dump($this->cachepath);
        }
        return $this;
    }

    protected function loadCache($file)
    {
        $this->cachepath = $file;
        if (self::CACHE_ACTIVE and file_exists($this->cachepath) and FileSystem::changetime($this->cachepath) > FileSystem::changetime($this->realpath)) {
            $this->resDoc = new DOMDocument();
            if ($this->resDoc->load($this->cachepath)) {
                $this->resNode = $this->resDoc->documentElement;
                return true;
            }
        }
        return false;
    }

    protected function saveCache()
    {
        if (self::CACHE_ACTIVE) {
            $this->resDoc->save($this->cachepath);
        }
    }

    protected function loadFileDefault()
    {
        $this->resNode->setAttribute('realpath', utf8_encode($this->getPath()));
        $this->resNode->setAttribute('uri', utf8_encode($this->getUri()));
    }

    protected function loadFileStatus()
    {
        $info = stat($this->getPath());
        $this->resNode->setAttribute('size', $info['size']);
        // *
        foreach ([
            'a' => 'access',
            'm' => 'change',
            'c' => 'inode'
        ] as $key => $attr) {
            $this->resNode->setAttribute($attr . '-stamp', $info[$key . 'time']);
            $this->resNode->setAttribute($attr . '-datetime', date(DATE_DATETIME, $info[$key . 'time']));
            $this->resNode->setAttribute($attr . '-utc', date(DATE_UTC, $info[$key . 'time']));
        }
    }

    protected function loadFileDocument()
    {
        $attrList = [];
        foreach ($this->resNode->attributes as $attrNode) {
            $attrList[$attrNode->name] = $attrNode->value;
        }
        $this->resDoc->load($this->getPath());
        if (! $this->resDoc) {
            throw new RuntimeException(sprintf('Resource::loadMode document failed ;A; (%s)', $this->getPath));
        }
        $this->resNode = $this->resDoc->documentElement;
        foreach ($attrList as $name => $value) {
            $this->resNode->setAttribute($name, $value);
        }
    }

    protected function loadFileXML()
    {}

    protected function loadFileBase64()
    {
        $this->resNode->setAttribute('base64', base64_encode($this->getContent()));
    }

    protected function loadFileThumbnail()
    {}

    protected function getContent()
    {
        return $this->fileData === null ? $this->fileData = file_get_contents($this->getPath()) : $this->fileData;
    }

    protected function getArray()
    {
        return file($this->getPath());
    }

    protected function value2dom(DOMDocument $doc, $value, $name = null)
    {
        $type = gettype($value);
        $node = $this->resDoc->createElement($type);
        if (is_string($name)) {
            $node->setAttribute('key', $name);
        }
        switch (true) {
            case is_object($value):
                foreach ($value as $key => $child) {
                    $node->appendChild($this->value2dom($doc, $child, $key));
                }
                break;
            case is_array($value):
                foreach ($value as $key => $child) {
                    $node->appendChild($this->value2dom($doc, $child, $key));
                }
                break;
            case is_string($value):
            case is_float($value):
            case is_int($value):
                $node->setAttribute('val', $value);
                break;
            case is_bool($value):
                $node->setAttribute($value ? 'on' : 'off', '');
                break;
        }
        return $node;
    }

    // filename, without path
    public function getFile()
    {
        return $this->file;
    }

    // absolute filesystem path
    public function getPath()
    {
        return $this->realpath;
    }

    // name, without path
    public function getName()
    {
        return urldecode(basename($this->uri));
    }

    // absolute uri path
    public function getUri()
    {
        return $this->uri;
    }

    //
    public function openHandle()
    {
        $this->fileHandle = fopen($this->getPath(), 'r');
    }

    public function closeHandle()
    {
        fclose($this->fileHandle);
    }

    // root element
    public function asElement()
    {
        return $this->resNode;
    }

    // whole document
    public function asDocument()
    {
        return $this->resDoc;
    }
    /*
     * public function __sleep() {
     * $this->resNode = $this->resDoc->saveXML($this->resNode);
     * $this->resDoc = $this->resDoc->saveXML();
     * return array(
     * 'path',
     * 'realpath',
     * 'uri',
     * 'resNode',
     * 'resDoc',
     * 'mimeType',
     * 'loaded',
     * 'loadFile',
     * 'options'
     * );
     * }
     * public function __wakeup() {
     * $doc = new DOMDocument('1.0', 'UTF-8');
     * $doc->loadXML($this->resNode);
     * $this->resNode = $doc->documentElement;
     * $doc->loadXML($this->resDoc);
     * $this->resDoc = $doc;
     * $this->resNode = $this->resDoc->importNode($this->resNode, true);
     *
     * }
     */
}
//Resource::construct();

