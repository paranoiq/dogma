<?php

namespace Dogma\Xml;

use Nette\Utils\Strings;


/**
 * Compiles and executes an XPath query
 */
class XpathProcessor extends \Dogma\Object {
    
    /** @var \DOMXPath */
    private $xpath;
    
    
    public $translates = array(
        // class: [.foo]
        "/\\[.([A-Za-z0-9_-]+)\\]/" => '[contains(concat(" ", normalize-space(@class), " "), " $1 ")]',

        // id: [#foo]
        "/\\[#([A-Za-z0-9_-]+)\\]/" => '[@id = "$1"]',

        // index: [n]
        "/\\[:first\\]/" => '[1]', // [:first]
        "/\\[:last\\]/"  => '[last()]', // [:last]
        "/\\[-([0-9]+)\\]/"  => '[position() = last() + 1 - $1]', // nth from end: [-n]
        "/\\[([0-9]+)..([0-9]+)\\]/" => '[position() >= $1 and position() <= $2]', // [m..n]
        "/\\[..([0-9]+)\\]/" => '[position() >= $1]', // [..n]
        "/\\[([0-9]+)..\\]/" => '[position() <= $1]', // [n..]
        "/\\[:even\\]/"  => '[position() mod 2]', // [:even]
        "/\\[:odd\\]/"   => '[not(position() mod 2)]', // [:odd]
        "/\\[:only\\]/"  => '[position() = 1 and position() = last()]', // [:only]

        // name: ["foo"]
        '/\\["([^"])"\\]/' => '[@name = "$1"]',
        "/\\['([^'])'\\]/" => '[@name = \'$1\']',

        // label: [label("foo")]
        '/\\[label\\("([^"])"\\)\\]/' => '[
                (ancestor::label[normalize-space() = "$1"]) or
                (@id = ancestor::form/descendant::label[normalize-space() = "$1"]/@for) or
                ((@type = "submit" or @type = "reset" or @type = "button") and @value = "$1") or
                (@type = "button" and normalize-space() = "$1")]',
        "/\\[label\\('([^'])'\\)\\]/" => '[
                (ancestor::label[normalize-space() = \'$1\']) or
                (@id = ancestor::form/descendant::label[normalize-space() = \'$1\']/@for) or
                ((@type = "submit" or @type = "reset" or @type = "button") and @value = \'$1\') or
                (@type = "button" and normalize-space() = \'$1\')]',

        // axes 'next' and 'previous'
        "#/previous::([A-Za-z0-9_-]+)#" => '/preceding-sibling::$1[last()]',
        "#/next::([A-Za-z0-9_-]+)#"     => '/following-sibling::$1[1]',

        // table shortcuts
        '/:headrow/' => "tr[name(..) = 'thead' or (name(..) = 'table' and not(../thead) and position() = 1)]",
        '/:bodyrow/' => "tr[name(..) = 'tbody' or (name(..) = 'table' and not(../tbody) and (../thead or position() != 1))]",
        '/:footrow/' => "tr[name(..) = 'tfoot' or (name(..) = 'table' and not(../tfoot) and position() = last()]",
        '/:cell/'    => "*[name() = 'td' or name() = 'th']",
        
        // jQuery-like shortcuts
        '/:input/' => "*[name() = 'input' or name() = 'textarea' or name() = 'select' or name() = 'button']",
        '/:file/'  => "input[@type = 'file']",
        '/:button/' => "*[name() = 'button' or (name() = 'input' and @type = 'button')]",
        '/:submit/' => "input[@type = 'submit']",
        '/:reset/' => "input[@type = 'reset']",
        '/:image/' => "input[@type = 'image']",
        '/:radio/' => "input[@type = 'radio']",
        '/:checkbox/' => "input[@type = 'checkbox']",
        '/:text/' => "*[name() = 'textarea' 
                or (name() = 'input' and (@type = 'text' or @type= 'hidden' or not(@type)))]",
        '/:password/' => "input[@type = 'password']",
        
        '/:header/' => "*[name() = 'h1' or name() = 'h2' or name() = 'h3' or name() = 'h4' or name() = 'h5' or name() = 'h6']",
        '/:link/'   => "a[@href]",
        '/:anchor/' => "*[@id or (name() = 'a' and @name)]",
        
        // function aliases
        '/int\\(/' => "number(",
        '/float\\(/' => "number(",
        '/bool\\(/' => "php:functionString('Dogma\\Xml\\XpathProcessor::bool', ",
        '/date\\(/' => "php:functionString('Dogma\\Xml\\XpathProcessor::date', ",
        '/match\\(/' => "php:functionString('Dogma\\Xml\\XpathProcessor::match', ",
        '/replace\\(/' => "php:functionString('Dogma\\Xml\\XpathProcessor::replace', ",
    );
    
    
    public static $nativeFunctions = array(
        'position',
        'last',
        'count',
        'id',
        'name',
        'local-name',
        'namespace-uri',
        
        'string',
        'concat',
        'starts-with',
        'contains',
        'substring',
        'substring-before',
        'substring-after',
        'string-length',
        'normalize-space',
        'translate',
        
        'boolean',
        'not',
        'true',
        'false',
        'lang',
        'number',
        'floor',
        'ceiling',
        'round',
        'sum',
        
        'function',
        'functionString',
        
        'match',
        'replace',
        'date'
    );


    public static $stringFunctions = array(
        'addcslashes',
        'addslashes',
        'bin2hex',
        'chop',
        'chr',
        'convert_cyr_string',
        'convert_uudecode',
        'convert_uuencode',
        'count_chars',
        'crc32',
        'crypt',
        'hebrev',
        'hebrevc',
        'hex2bin',
        'html_entity_decode',
        'htmlentities',
        'htmlspecialchars_decode',
        'htmlspecialchars',
        'lcfirst',
        'levenshtein',
        'localeconv',
        'ltrim',
        'md5',
        'metaphone',
        'money_format',
        'nl_langinfo',
        'nl2br',
        'number_format',
        'quoted_printable_decode',
        'quoted_printable_encode',
        'quotemeta',
        'rtrim',
        'sha1',
        'similar_text',
        'soundex',
        'sprintf',
        'sscanf',
        'str_getcsv',
        'str_ireplace',
        'str_pad',
        'str_repeat',
        'str_replace',
        'str_rot13',
        'str_shuffle',
        'str_split',
        'str_word_count',
        'strcasecmp',
        'strchr',
        'strcmp',
        'strcoll',
        'strcspn',
        'strip_tags',
        'stripcslashes',
        'stripos',
        'stripslashes',
        'stristr',
        'strlen',
        'strnatcasecmp',
        'strnatcmp',
        'strncasecmp',
        'strncmp',
        'strpbrk',
        'strpos',
        'strrchr',
        'strrev',
        'strripos',
        'strrpos',
        'strspn',
        'strstr',
        'strtok',
        'strtolower',
        'strtoupper',
        'strtr',
        'substr_compare',
        'substr_count',
        'substr_replace',
        'substr',
        'trim',
        'ucfirst',
        'ucwords',
        'vsprintf',
        'wordwrap',
        
        'preg_match',
        'preg_quote',
        'preg_replace',
        
        'Dogma\\Xml\\XpathProcessor::match',
        'Dogma\\Xml\\XpathProcessor::replace',
        'Dogma\\Xml\\XpathProcessor::date',
        'Dogma\\Xml\\XpathProcessor::bool',
    );
    
    
    private static $mbFunctions = array(
        'mb_convert_case',
        'mb_convert_encoding',
        'mb_convert_kana',
        'mb_decode_mimeheader',
        'mb_decode_numericentity',
        'mb_encode_mimeheader',
        'mb_encode_numericentity',
        'mb_split',
        'mb_strcut',
        'mb_strimwidth',
        'mb_stripos',
        'mb_stristr',
        'mb_strlen',
        'mb_strpos',
        'mb_strrchr',
        'mb_strrichr',
        'mb_strripos',
        'mb_strrpos',
        'mb_strstr',
        'mb_strtolower',
        'mb_strtoupper',
        'mb_strwidth',
        'mb_substitute_character',
        'mb_substr_count',
        'mb_substr',
    );
    
    
    private static $iconvFunctions = array(
        'iconv_mime_decode',
        'iconv_mime_encode',
        'iconv_strlen',
        'iconv_strpos',
        'iconv_strrpos',
        'iconv_substr',
        'iconv',
    );
    
    
    public static $mathFunctions = array(
        'abs',
        'acos',
        'acosh',
        'asin',
        'asinh',
        'atan2',
        'atan',
        'atanh',
        'base_convert',
        'bindec',
        //'ceil',
        'cos',
        'cosh',
        'decbin',
        'dechex',
        'decoct',
        'deg2rad',
        'exp',
        'expm1',
        //'floor',
        'fmod',
        //'getrandmax',
        'hexdec',
        'hypot',
        //'is_finite',
        //'is_infinite',
        //'is_nan',
        //'lcg_value',
        'log10',
        'log1p',
        'log',
        'max',
        'min',
        //'mt_getrandmax',
        //'mt_rand',
        //'mt_srand',
        'octdec',
        'pi',
        'pow',
        'rad2deg',
        //'rand',
        //'round',
        'sin',
        'sinh',
        'sqrt',
        //'srand',
        'tan',
        'tanh',
    );
    
    
    private static $init;
    
    
    public function __construct(\DOMDocument $dom) {
        $this->xpath = new \DOMXPath($dom);
        $this->xpath->registerNamespace("php", "http://php.net/xpath");
        
        if (!self::$init) {
            if (extension_loaded('iconv')) 
                self::$stringFunctions = array_merge(self::$stringFunctions, self::$iconvFunctions);
            if (extension_loaded('mb_string'))
                self::$stringFunctions = array_merge(self::$stringFunctions, self::$mbFunctions);
            self::$init = TRUE;
        }
            
        $this->xpath->registerPhpFunctions(array_merge(self::$stringFunctions, self::$mathFunctions));
    }
    
    
    /**
     * Translates Zpath to pure Xpath syntax
     * @param $path
     * @param bool|NULL
     * @return string
     * @throws \DOMException
     */
    private function translate($path, $context = FALSE) {
        if ($context === TRUE) {
            if ($path[0] === '/') {
                $path = '.' . $path;
            } elseif ($path[0] !== '.') {
                $path = './/' . $path;
            }
        } elseif ($context === FALSE) {
            if ($path[0] !== '/') $path = '//' . $path;
        }
        
        $path = Strings::replace($path, $this->translates);
        
        $path = Strings::replace($path, '/(?<![A-Za-z0-9_-])([A-Za-z0-9_-]+)\\(/', 
        function ($match) {
            
            if (in_array($match[1], XpathProcessor::$nativeFunctions)) {
                return $match[1] . '(';

            } elseif (in_array($match[1], XpathProcessor::$stringFunctions)) {
                return "php:functionString('$match[1]', ";

            } elseif (in_array($match[1], XpathProcessor::$mathFunctions)) {
                return "php:function('$match[1]', ";

            } else {
                throw new \DOMException("XPath compilation failure: Functions '$match[1]' is not enabled.");
            }
        });
        
        return $path;
    }

    
    /**
     * Test with regular expression and return matching string
     * @param string
     * @param string
     * @return string
     */
    static public function match($string, $pattern) {
        if ($m = Strings::match($string, $pattern)) return $m[0];
        return "";
    }

    
    /**
     * Replace substring with regular expression
     * @param string
     * @param string
     * @param string
     * @return string
     */
    static public function replace($string, $pattern, $replacement) {
        return Strings::replace($string, $pattern, $replacement);
    }
    
    
    /**
     * Format date in standard ISO format Y-m-d
     * @param string
     * @param string
     * @return string
     */
    static public function date($string, $format = 'Y-m-d') {
        $date = \DateTime::createFromFormat($format, $string);
        if (!$date) 
            throw new XpathException("Cannot create DateTime object from '$string' using format '$format'.");
        
        return $date->format('Y-m-d');
    }
    
    
    /**
     * Lookup boolean values as given.
     * @param string
     * @param string
     * @param string
     * @return bool|NULL
     */
    static public function bool($string, $true = 'TRUE', $false = 'FALSE') {
        $string = strtoupper($string);
        if ($string === $false) return 0;
        if ($string === $true)  return 1;
        return NULL;
    }
    
    
    /**
     * Find nodes using Zpath query
     * @param string
     * @param \DOMNode
     * @return DomNodeList
     */
    public function find($path, $context = NULL) {
        $xpath = $this->translate($path, (bool) $context);
        if ($context) {
            $list = $this->xpath->query($xpath, $context);
        } else {
            $list = $this->xpath->query($xpath);
        }
        if ($list === FALSE)
            throw new XpathException("Invalid xpath query: \"$xpath\", translated from: \"$path\".");
        
        return new DomNodeList($list, $this);
    }


    /**
     * Find one node using Zpath query
     * @param string
     * @param \DOMNode
     * @return \DOMNode|DomElement|NULL
     */
    public function findOne($path, $context = NULL) {
        $xpath = $this->translate($path, (bool) $context);
        if ($context) {
            $list = $this->xpath->query($xpath, $context);
        } else {
            $list = $this->xpath->query($xpath);
        }
        if ($list === FALSE)
            throw new XpathException("Invalid xpath query: \"$xpath\", translated from: \"$path\".");
        
        if (!count($list)) return NULL;
        
        return $this->wrap($list->item(0));
    }


    /**
     * Evaluate an Zpath expression
     * @param string
     * @param \DOMNode
     * @return string|int|float
     */
    public function evaluate($path, $context = NULL) {
        $xpath = $this->translate($path, NULL);
        if ($context) {
            $value = $this->xpath->evaluate($xpath, $context);
        } else {
            $value = $this->xpath->evaluate($xpath);
        }
        if ($value === FALSE)
            throw new XpathException("Invalid xpath query: \"$xpath\", translated from: \"$path\".");
        
        if (substr($path, 0, 5) === 'date(') {
            return new \Dogma\Date($value);
            
        } elseif (substr($path, 0, 4) === 'int(') {
            return (int) $value;

        } elseif (substr($path, 0, 5) === 'bool(' && isset($value)) {
            return (bool) $value;

        } else {
            return $value;
        }
    }


    /**
     * Etract values from paths defined by Zpath queries
     * @param string|string[]
     * @param DomNode|\DOMNode
     * @return string|string[]
     */
    public function extract($target, $context = NULL) {
        if (is_string($target)) {
            return $this->extractPath($target, $context);
        }
        
        $value = array();
        foreach ($target as $i => $path) {
            $value[$i] = $this->extractPath($path, $context);
        }
        return $value;
    }

    
    /**
     * @param $path
     * @param $context
     * @return string|int|float|\DateTime|null
     */
    private function extractPath($path, $context) {
        if (Strings::match($path, '/^[a-zA-Z0-9_]+\\(/')) {
            $node = $this->evaluate($path, $context);
        } else {
            $xpath = $this->translate($path, (bool) $context);
            $node = $this->findOne($xpath, $context);
        }

        if (is_scalar($node) || $node instanceof \DateTime) {
            return $node;
            
        } elseif (!$node) {
            return NULL;

        } elseif ($node instanceof \DOMAttr) {
            return $node->value;
            
        } elseif ($node instanceof \DOMText) {
            return $node->wholeText;
            
        } elseif ($node instanceof \DOMCdataSection || $node instanceof \DOMComment || $node instanceof \DOMProcessingInstruction) {
            return $node->data;
            
        } else {
            return $node->textContent;
        }
    }
    
    
    /**
     * Wrap element in DomElement object
     * @param \DOMNode
     * @return DomElement|\DOMNode
     */
    private function wrap($node) {
        if ($node instanceof \DOMElement) {
            return new DomElement($node, $this);
        } else {
            return $node;
        }
    }
    
}
