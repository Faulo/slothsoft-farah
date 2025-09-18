<?php
declare(strict_types = 1);
// © 2012 Daniel Schulz
namespace Slothsoft\Farah;

use Slothsoft\Core\DOMHelper;
use Slothsoft\Core\Configuration\ConfigurationField;
use Slothsoft\Farah\FarahUrl\FarahUrl;
use Slothsoft\Farah\Module\Module;
use DOMDocument;
use DOMElement;
use DOMNode;
use DOMXPath;

class Dictionary {
    
    private static function supportedLanguages() {
        static $field;
        if ($field === null) {
            $field = new ConfigurationField([]);
        }
        return $field;
    }
    
    public static function setSupportedLanguages(string ...$languageList) {
        self::supportedLanguages()->setValue($languageList);
    }
    
    public static function getSupportedLanguages(): array {
        return self::supportedLanguages()->getValue();
    }
    
    const BCP47_PREGMATCH = '/(?<language>[a-z]{2,3})(?:-(?<extlang>aao|abh|abv|acm|acq|acw|acx|acy|adf|ads|aeb|aec|aed|aen|afb|afg|ajp|apc|apd|arb|arq|ars|ary|arz|ase|asf|asp|asq|asw|auz|avl|ayh|ayl|ayn|ayp|bbz|bfi|bfk|bjn|bog|bqn|bqy|btj|bve|bvl|bvu|bzs|cdo|cds|cjy|cmn|coa|cpx|csc|csd|cse|csf|csg|csl|csn|csq|csr|czh|czo|doq|dse|dsl|dup|ecs|esl|esn|eso|eth|fcs|fse|fsl|fss|gan|gds|gom|gse|gsg|gsm|gss|gus|hab|haf|hak|hds|hji|hks|hos|hps|hsh|hsl|hsn|icl|ils|inl|ins|ise|isg|isr|jak|jax|jcs|jhs|jls|jos|jsl|jus|kgi|knn|kvb|kvk|kvr|kxd|lbs|lce|lcf|liw|lls|lsg|lsl|lso|lsp|lst|lsy|ltg|lvs|lzh|max|mdl|meo|mfa|mfb|mfs|min|mnp|mqg|mre|msd|msi|msr|mui|mzc|mzg|mzy|nan|nbs|ncs|nsi|nsl|nsp|nsr|nzs|okl|orn|ors|pel|pga|pks|prl|prz|psc|psd|pse|psg|psl|pso|psp|psr|pys|rms|rsi|rsl|sdl|sfb|sfs|sgg|sgx|shu|slf|sls|sqk|sqs|ssh|ssp|ssr|svk|swc|swh|swl|syy|tmw|tse|tsm|tsq|tss|tsy|tza|ugn|ugy|ukl|uks|urk|uzn|uzs|vgt|vkk|vkt|vsi|vsl|vsv|wuu|xki|xml|xmm|xms|yds|ysl|yue|zib|zlm|zmi|zsl|zsm))?(?:-(?<script>afak|aghb|ahom|arab|armi|armn|avst|bali|bamu|bass|batk|beng|blis|bopo|brah|brai|bugi|buhd|cakm|cans|cari|cham|cher|cirt|copt|cprt|cyrl|cyrs|deva|dsrt|dupl|egyd|egyh|egyp|elba|ethi|geok|geor|glag|goth|gran|grek|gujr|guru|hang|hani|hano|hans|hant|hatr|hebr|hira|hluw|hmng|hrkt|hung|inds|ital|java|jpan|jurc|kali|kana|khar|khmr|khoj|knda|kore|kpel|kthi|lana|laoo|latf|latg|latn|lepc|limb|lina|linb|lisu|loma|lyci|lydi|mahj|mand|mani|maya|mend|merc|mero|mlym|modi|mong|moon|mroo|mtei|mult|mymr|narb|nbat|nkgb|nkoo|nshu|ogam|olck|orkh|orya|osma|palm|pauc|perm|phag|phli|phlp|phlv|phnx|plrd|prti|rjng|roro|runr|samr|sara|sarb|saur|sgnw|shaw|shrd|sidd|sind|sinh|sora|sund|sylo|syrc|syre|syrj|syrn|tagb|takr|tale|talu|taml|tang|tavt|telu|teng|tfng|tglg|thaa|thai|tibt|tirh|ugar|vaii|visp|wara|wole|xpeo|xsux|yiii|zinh|zmth|zsym|zxxx|zyyy|zzzz))?(?:-(?<region>ac|ad|ae|af|ag|ai|al|am|an|ao|aq|ar|as|at|au|aw|ax|az|ba|bb|bd|be|bf|bg|bh|bi|bj|bl|bm|bn|bo|bq|br|bs|bt|bu|bv|bw|by|bz|ca|cc|cd|cf|cg|ch|ci|ck|cl|cm|cn|co|cp|cr|cs|cu|cv|cw|cx|cy|cz|dd|de|dg|dj|dk|dm|do|dz|ea|ec|ee|eg|eh|er|es|et|eu|fi|fj|fk|fm|fo|fr|fx|ga|gb|gd|ge|gf|gg|gh|gi|gl|gm|gn|gp|gq|gr|gs|gt|gu|gw|gy|hk|hm|hn|hr|ht|hu|ic|id|ie|il|im|in|io|iq|ir|is|it|je|jm|jo|jp|ke|kg|kh|ki|km|kn|kp|kr|kw|ky|kz|la|lb|lc|li|lk|lr|ls|lt|lu|lv|ly|ma|mc|md|me|mf|mg|mh|mk|ml|mm|mn|mo|mp|mq|mr|ms|mt|mu|mv|mw|mx|my|mz|na|nc|ne|nf|ng|ni|nl|no|np|nr|nt|nu|nz|om|pa|pe|pf|pg|ph|pk|pl|pm|pn|pr|ps|pt|pw|py|qa|re|ro|rs|ru|rw|sa|sb|sc|sd|se|sg|sh|si|sj|sk|sl|sm|sn|so|sr|ss|st|su|sv|sx|sy|sz|ta|tc|td|tf|tg|th|tj|tk|tl|tm|tn|to|tp|tr|tt|tv|tw|tz|ua|ug|um|us|uy|uz|va|vc|ve|vg|vi|vn|vu|wf|ws|yd|ye|yt|yu|za|zm|zr|zw|001|002|003|005|009|011|013|014|015|017|018|019|021|029|030|034|035|039|053|054|057|061|142|143|145|150|151|154|155|419))?(?:-(?<variant>1606nict|1694acad|1901|1959acad|1994|1996|alalc97|aluku|arevela|arevmda|baku1926|barla|bauddha|biscayan|biske|bohoric|boont|dajnko|ekavsk|emodeng|fonipa|fonupa|fonxsamp|hepburn|heploc|hognorsk|ijekavsk|itihasa|jauer|jyutping|kkcor|kscor|laukika|lipaw|luna1918|metelko|monoton|ndyuka|nedis|njiva|nulik|osojs|pamaka|petr1708|pinyin|polyton|puter|rigik|rozaj|rumgr|scotland|scouse|solba|sotav|surmiran|sursilv|sutsilv|tarask|uccor|ucrcor|ulster|unifon|vaidika|valencia|vallader|wadegile))?/';
    
    public static function parseAcceptLanguageHeader(string $language): array {
        $match = [];
        return preg_match_all(self::BCP47_PREGMATCH, strtolower($language), $match, PREG_SET_ORDER) ? $match : [];
    }
    
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
    
    /**
     *
     * @var FarahUrl
     */
    protected $currentModule;
    
    protected $currentLang;
    
    protected $currentNS = 'core';
    
    protected $langDocPath = 'vendor/slothsoft/%s/lang.%s.xml';
    
    protected $langDocs = [];
    
    protected $langPaths = [];
    
    protected static $instance;
    
    /* static functions */
    public static function getInstance(): Dictionary {
        static $instance;
        if ($instance === null) {
            $instance = new Dictionary();
        }
        return $instance;
    }
    
    public static function lookup($word, $namespace = null, $language = null) {
        $dict = self::getInstance();
        return $dict->lookupText($word, $namespace, $language);
    }
    
    public static function createLink($uri, $lang = null) {
        return $uri . '?' . self::KEY_SET_LANG . '=' . $lang;
    }
    
    public static function languageInfo($rawCode) {
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
    
    private function __construct() {
        if (isset($_REQUEST[self::KEY_SET_LANG])) {
            setcookie(self::KEY_REQUEST_LANG, $_REQUEST[self::KEY_SET_LANG], time() + 60 * 60 * 24 * 30, '/');
            $_REQUEST[self::KEY_REQUEST_LANG] = $_REQUEST[self::KEY_SET_LANG];
        }
        $this->calcAcceptLanguage();
    }
    
    private function calcAcceptLanguage() {
        $this->currentLang = self::getSupportedLanguages()[0] ?? null;
        
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
                break;
            }
        }
    }
    
    private $isSettingUp = false;
    
    /* public functions */
    public function translateDoc(DOMDocument $doc, FarahUrl $context) {
        if ($this->isSettingUp) {
            return 0;
        }
        $this->isSettingUp = true;
        
        $this->currentModule = FarahUrl::createFromComponents($context->getAssetAuthority());
        
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
        if ($lang = $this->getLang()) {
            $doc->documentElement->setAttribute('xml:lang', $lang);
        }
        
        $this->isSettingUp = false;
        
        return $ret ? $ret + $this->translateDoc($doc, $context) : 0;
    }
    
    public function translateNode(DOMXPath $xpath, DOMElement $node, $expr, $namespace = null, $language = null) {
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
    
    public function acceptLanguage($acceptLang) {
        $acceptArr = explode(',', $acceptLang);
        foreach ($acceptArr as $lang) {
            $lang = explode(';', $lang);
            $lang = strtolower(trim(current($lang)));
            foreach (self::getSupportedLanguages() as $supLang) {
                if (substr($supLang, 0, 2) === substr($lang, 0, 2)) {
                    $this->setLang($supLang);
                    // $this->currentLang = $supLang;
                    return true;
                }
            }
        }
        return false;
    }
    
    public function setLang($lang) {
        $ret = $this->currentLang;
        $this->currentLang = $lang;
        return $ret;
    }
    
    public function setNS($ns) {
        $ret = $this->currentNS;
        $this->currentNS = $ns;
        return $ret;
    }
    
    public function getLang() {
        return $this->currentLang;
    }
    
    public function getNS() {
        return $this->currentNS;
    }
    
    public function lookupText($originalWord, $namespace = null, $language = null) {
        $word = $this->sanitizeWord($originalWord);
        $xpath = $this->getLangPath($namespace, $language);
        if (! $xpath->evaluate(sprintf(self::XPATH_EXISTS, $word))) {
            $this->addWord($xpath->document, $word, $originalWord);
        }
        return $xpath->evaluate(sprintf(self::XPATH_TEXT, $word));
    }
    
    public function lookupXML($word, $namespace = null, $language = null) {
        $word = $this->sanitizeWord($word);
    }
    
    public function lookupFragment($originalWord, $namespace = null, $language = null, DOMDocument $ownerDoc = null) {
        if (! ($originalWord instanceof DOMNode)) {
            $originalWord = (string) $originalWord;
        }
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
            if ($originalWord instanceof DOMNode) {
                $ret->appendChild($originalWord->ownerDocument === $ownerDoc ? $originalWord->cloneNode(true) : $ownerDoc->importNode($originalWord, true));
            } else {
                $ret->appendChild($ownerDoc->createTextNode($originalWord));
            }
        }
        return $ret;
    }
    
    /* private functions */
    protected function sanitizeWord($word) {
        if ($word instanceof DOMNode) {
            $word = $word->textContent;
        }
        return trim(str_replace([
            '"',
            "'"
        ], '', $word));
    }
    
    protected function getLangPath($namespace = null, $language = null) {
        if ($namespace === null) {
            $namespace = '';
        } else {
            $namespace = "farah://$namespace";
        }
        if ($language === null) {
            $language = $this->currentLang;
        }
        
        $ref = "$namespace/dictionary/$language#xml"; // TODO: make this less presuming
        
        $url = FarahUrl::createFromReference($ref, $this->currentModule);
        $key = (string) $url;
        
        if (! isset($this->langPaths[$key])) {
            $doc = Module::resolveToDOMWriter($url)->toDocument();
            $this->langPaths[$key] = DOMHelper::loadXPath($doc, DOMHelper::XPATH_SLOTHSOFT | DOMHelper::XPATH_HTML);
        }
        return $this->langPaths[$key];
    }
    
    protected function addWord(DOMDocument $doc, $word, $originalWord = null) {
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
