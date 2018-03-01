<?php

declare(strict_types = 1);
// © 2012 Daniel Schulz
namespace Slothsoft\Farah;

use Slothsoft\Core\DOMHelper;
use Slothsoft\Farah\Module\Module;
use Slothsoft\Farah\Module\FarahUrl\FarahUrl;
use Slothsoft\Farah\Module\FarahUrl\FarahUrlResolver;
use DOMDocument;
use DOMElement;
use DOMNode;
use DOMXPath;

class Dictionary
{

    const NS_HTML = 'http://www.w3.org/1999/xhtml';

    const KEY_REQUEST_LANG = 'HTTP_ACCEPT_LANGUAGE';

    const KEY_SET_LANG = 'lang';

    const ERR_NS_NOTFOUND = 'Module Directory not found for Namespace "%s"!';

    const XPATH_LANGNODE = 'p';

    const XPATH_LANGATTR = 'key';

    const XPATH_EOL = "\n";

    const XPATH_EXISTS = 'boolean(/html:html/html:p[@key="%s"])';

    const XPATH_TEXT = 'string(/html:html/html:p[@key="%s"])';

    const XPATH_FRAGMENT = '/html:html/html:p[@key="%s"]/node()';

    const XPATH_DICT_ATTR_SELECT = 'data-dict';

    const XPATH_DICT_ATTR_NS = 'data-dict-ns';

    const XPATH_DICT_ATTR_LANG = 'data-dict-lang';

    const XPATH_DICT_ATTR_REPLACE = 'data-dict-replace';

    const XPATH_DICT_DEFAULT = 'node()';

    const XPATH_DICT_REPLACE = '.';

    protected $currentModule;

    protected $currentLang;

    protected $currentNS = 'core';

    protected $langDocPath = 'vendor/slothsoft/%s/lang.%s.xml';

    protected $langDocs = [];

    protected $langPaths = [];

    protected static $instance;

    protected static $supportedLang;

    /* static functions */
    public static function getInstance() : Dictionary
    {
        static $instance;
        if ($instance === null) {
            $instance = new Dictionary();
        }
        return $instance;
    }

    public static function lookup($word, $namespace = null, $language = null)
    {
        $dict = self::getInstance();
        return $dict->lookupText($word, $namespace, $language);
    }

    public static function createLink($uri, $lang = null)
    {
        return $uri . '?' . self::KEY_SET_LANG . '=' . $lang;
    }

    public static function languageInfo($rawCode)
    {
        $rawCode = trim($rawCode);
        $ret = [
            'source' => $rawCode,
            'code' => 'und',
            'name' => 'Undetermined'
        ];
        if ($httpDocument = Kernel::getInstance()) {
            $registryDoc = $httpDocument->getResourceDoc('/core/language-registry', 'xml');
            $registryPath = DOMHelper::loadXPath($registryDoc);
            $iso639Doc = $httpDocument->getResourceDoc('/core/iso-639', 'xml');
            $iso639Path = DOMHelper::loadXPath($iso639Doc);
            
            if (strlen($rawCode) === 3) {
                $langCode = $iso639Path->evaluate(sprintf('string(//tr[td[1][contains(., "%s")]]/td[2])', $rawCode));
                $ret['code'] = $langCode;
                $langName = $registryPath->evaluate(sprintf('string(//registry/language[subtag = "%s"]/description)', $langCode));
                $ret['name'] = $langName;
            }
            if ($ret['code'] === 'und') {
                $langName = $registryPath->evaluate(sprintf('string(//registry/language[subtag = "%s"]/description)', $rawCode));
                if (strlen($langName)) {
                    $ret['code'] = $rawCode;
                    $ret['name'] = $langName;
                }
            }
        }
        return $ret;
    }

    private function __construct()
    {
        if (isset($_REQUEST[self::KEY_SET_LANG])) {
            setcookie(self::KEY_REQUEST_LANG, $_REQUEST[self::KEY_SET_LANG], time() + 60 * 60 * 24 * 30, '/');
            $_REQUEST[self::KEY_REQUEST_LANG] = $_REQUEST[self::KEY_SET_LANG];
        }
        $this->setSupportedLanguages(DICT_LANGUAGES);
    }

    public function setSupportedLanguages($languages)
    {
        $arr = explode(' ', $languages);
        self::$supportedLang = [];
        foreach ($arr as $tmp) {
            $tmp = trim($tmp);
            if (strlen($tmp)) {
                self::$supportedLang[] = $tmp;
            }
        }
        if (count(self::$supportedLang)) {
            $this->currentLang = reset(self::$supportedLang);
        } else {
            $this->currentLang = null;
        }
        $langReqs = [];
        if (isset($_REQUEST)) {
            $langReqs[] = $_REQUEST;
        }
        if (isset($_COOKIE)) {
            $langReqs[] = $_COOKIE;
        }
        if (isset($_SERVER)) {
            $langReqs[] = $_SERVER;
        }
        foreach ($langReqs as $req) {
            if (isset($req[self::KEY_REQUEST_LANG]) and $this->acceptLanguage($req[self::KEY_REQUEST_LANG])) {
                $found = true;
                break;
            }
        }
    }

    /* public functions */
    public function translateDoc(DOMDocument $doc, Module $context)
    {
        $this->currentModule = $context;
        
        $ret = 0;
        $xpath = DOMHelper::loadXPath($doc, DOMHelper::XPATH_NS_ALL);
        
        // data-dict-replace
        $res = $xpath->evaluate(sprintf('//*[@%s]', self::XPATH_DICT_ATTR_REPLACE));
        $nodeList = [];
        foreach ($res as $node) {
            $nodeList[] = $node;
        }
        foreach ($nodeList as $node) {
            $attr = $node->getAttribute(self::XPATH_DICT_ATTR_REPLACE);
            $node->removeAttribute(self::XPATH_DICT_ATTR_REPLACE);
            $node->setAttribute(self::XPATH_DICT_ATTR_SELECT, self::XPATH_DICT_REPLACE);
            $node->textContent = $attr;
        }
        
        // data-dict
        $res = $xpath->evaluate(sprintf('//*[@%s]', self::XPATH_DICT_ATTR_SELECT));
        $nodeList = [];
        foreach ($res as $node) {
            $nodeList[] = $node;
        }
        foreach ($nodeList as $node) {
            $attr = $node->getAttribute(self::XPATH_DICT_ATTR_SELECT);
            $node->removeAttribute(self::XPATH_DICT_ATTR_SELECT);
            if (! strlen($attr)) {
                $attr = self::XPATH_DICT_DEFAULT;
            }
            $namespace = $node->hasAttribute(self::XPATH_DICT_ATTR_NS) ? $node->getAttribute(self::XPATH_DICT_ATTR_NS) : null;
            $language = $node->hasAttribute(self::XPATH_DICT_ATTR_LANG) ? $node->getAttribute(self::XPATH_DICT_ATTR_LANG) : null;
            $ret += $this->translateNode($xpath, $node, $attr, $namespace, $language);
        }
        $doc->documentElement->setAttribute('xml:lang', $this->getLang());
        return $ret ? $ret + $this->translateDoc($doc, $context) : 0;
    }

    public function translateNode(DOMXPath $xpath, DOMElement $node, $expr, $namespace = null, $language = null)
    {
        $ret = 0;
        $res = $xpath->evaluate($expr, $node);
        $nodeList = [];
        foreach ($res as $node) {
            $nodeList[] = $node;
        }
        foreach ($nodeList as $node) {
            $replaceNode = $node;
            if ($expr === self::XPATH_DICT_REPLACE) {
                $replaceNode = $this->sanitizeWord($replaceNode);
            }
            switch ($node->nodeType) {
                case XML_ELEMENT_NODE:
                    $node->parentNode->replaceChild($this->lookupFragment($replaceNode, $namespace, $language, $node->ownerDocument), $node);
                    $ret ++;
                    break;
                case XML_ATTRIBUTE_NODE:
                    $node->value = $this->lookupText($node->value);
                    $ret ++;
                    break;
                case XML_TEXT_NODE:
                    $node->parentNode->replaceChild($this->lookupFragment($node->data, $namespace, $language, $node->ownerDocument), $node);
                    $ret ++;
                    break;
            }
        }
        return $ret;
    }

    public function acceptLanguage($acceptLang)
    {
        $acceptArr = explode(',', $acceptLang);
        foreach ($acceptArr as $lang) {
            $lang = explode(';', $lang);
            $lang = strtolower(trim(current($lang)));
            foreach (self::$supportedLang as $supLang) {
                if (substr($supLang, 0, 2) === substr($lang, 0, 2)) {
                    $this->setLang($supLang);
                    // $this->currentLang = $supLang;
                    return true;
                }
            }
        }
        return false;
    }

    public function setLang($lang)
    {
        $ret = $this->currentLang;
        $this->currentLang = $lang;
        return $ret;
    }

    public function setNS($ns)
    {
        $ret = $this->currentNS;
        $this->currentNS = $ns;
        return $ret;
    }

    public function setModule(Module $module)
    {
        $this->currentModule = $module;
    }

    public function getLang()
    {
        return $this->currentLang;
    }

    public function getNS()
    {
        return $this->currentNS;
    }

    public function getSupportedLang()
    {
        return self::$supportedLang;
    }

    public function lookupText($originalWord, $namespace = null, $language = null)
    {
        $word = $this->sanitizeWord($originalWord);
        $xpath = $this->getLangPath($namespace, $language);
        if (! $xpath->evaluate(sprintf(self::XPATH_EXISTS, $word))) {
            $this->addWord($xpath->document, $word, $originalWord);
        }
        return $xpath->evaluate(sprintf(self::XPATH_TEXT, $word));
    }

    public function lookupXML($word, $namespace = null, $language = null)
    {
        $word = $this->sanitizeWord($word);
    }

    public function lookupFragment($originalWord, $namespace = null, $language = null, DOMDocument $ownerDoc = null)
    {
        $word = $this->sanitizeWord($originalWord);
        $xpath = $this->getLangPath($namespace, $language);
        if (! $xpath->evaluate(sprintf(self::XPATH_EXISTS, $word))) {
            $this->addWord($xpath->document, $word, $originalWord);
        }
        if ($ownerDoc === null) {
            $ownerDoc = $xpath->document;
        }
        $ret = $ownerDoc->createDocumentFragment();
        $res = $xpath->evaluate(sprintf(self::XPATH_FRAGMENT, $word));
        foreach ($res as $node) {
            $ret->appendChild($node->ownerDocument === $ownerDoc ? $node->cloneNode(true) : $ownerDoc->importNode($node, true));
        }
        if (! $ret->hasChildNodes()) {
            $ret->appendChild($ownerDoc->createTextNode($originalWord));
        }
        return $ret;
    }

    /* private functions */
    protected function sanitizeWord($word)
    {
        if ($word instanceof DOMNode) {
            $word = $word->textContent;
        }
        return trim(str_replace([
            '"',
            "'"
        ], '', $word));
    }

    protected function getLangPath($namespace = null, $language = null)
    {
        if ($namespace === null) {
            $namespace = $this->currentModule->getId();
        } else {
            $namespace = "farah://$namespace";
        }
        if ($language === null) {
            $language = $this->currentLang;
        }
        
        $ref = "$namespace/dictionary/$language"; // TODO: make this less presumptive
        $url = FarahUrl::createFromReference($ref, $this->currentModule->getAuthority());
        $key = (string) $url;
        
        if (! isset($this->langPaths[$key])) {
            $asset = FarahUrlResolver::resolveToResult($url);
            if ($asset->exists()) {
                $doc = $asset->toDocument();
            } else {
                $doc = new DOMDocument();
            }
            $this->langPaths[$key] = DOMHelper::loadXPath($doc, DOMHelper::XPATH_SLOTHSOFT | DOMHelper::XPATH_HTML);
        }
        return $this->langPaths[$key];
    }

    protected function addWord(DOMDocument $doc, $word, $originalWord = null)
    {
        return;
        
        $word = trim($word);
        if (strlen($word)) {
            $node = $doc->createElementNS(self::NS_HTML, self::XPATH_LANGNODE);
            $node->setAttribute(self::XPATH_LANGATTR, $word);
            if ($originalWord === null) {
                $originalWord = $word;
            }
            if ($originalWord instanceof DOMNode) {
                $child = $doc->importNode($originalWord, true);
            } else {
                $child = $doc->createTextNode($originalWord);
            }
            $node->appendChild($child);
            $doc->documentElement->appendChild($node);
            $doc->documentElement->appendChild($doc->createTextNode(self::XPATH_EOL));
            $doc->save($doc->documentURI);
        }
    }
}
