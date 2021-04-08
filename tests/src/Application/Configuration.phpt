<?php declare(strict_types = 1);

namespace Dogma\Tests\Enum;

use Dogma\Application\Colors as C;
use Dogma\Application\Configuration;
use Dogma\Application\Configurator;
use Dogma\Application\Console;
use Dogma\Enum\IntEnum;
use Dogma\InvalidTypeException;
use Dogma\Math\BytesCalc;
use Dogma\Str;
use Dogma\Tester\Assert;
use ReflectionClass;
use function ini_set;
use function preg_match;
use function sprintf;

require_once __DIR__ . '/../bootstrap.php';

class Config extends Configuration
{

    /** @var Path|Path[]|null */
    public $config;

    /** @var string|null */
    public $use = null;

    /** @var int|null */
    public $memoryLimit = null;

    /** @var bool */
    public $debug = false;

    /** @var Path */
    public $baseDir;


    /** @var Path|null */
    public $filesBaseDir;

    /** @var string[] */
    public $files = [];

    /** @var string[] */
    public $directories = [];

    /** @var string[] */
    public $extensions = [];


    /** @var Path|null */
    public $dictionariesBaseDir;

    /** @var string[] */
    public $dictionaryDirectories = [];

    /** @var string[] */
    public $dictionaries = [];

    /** @var array<string, string[]> */
    public $dictionariesByFileName = [];

    /** @var array<string, string[]> */
    public $dictionariesByFileExtension = [];

    /** @var string[] */
    public $dictionariesWithDiacritics = [];


    /** @var array<string, string[]> */
    public $localIgnores;

    /** @var bool */
    public $checkLocalIgnores = true;

    /** @var bool */
    public $checkDictionaryFiles = true;

    /** @var string[]|null */
    public $dictionaryFilesToCheck;

    /** @var bool */
    public $short = false;

    /** @var bool */
    public $topWords = false;

    /** @var int */
    public $maxErrors;

    /** @var string[] */
    public $wordsParserExceptions = ['PHPUnit'];

    /** @var bool */
    public $ignoreUrls = false;

    /** @var bool */
    public $ignoreEmails = false;


    /** @var bool */
    public $help = false;

    /** @var bool */
    public $license = false;

    /** @var bool */
    public $noColors = false;

    /** @var bool */
    public $noLogo = false;


    /** @var Console */
    private $console;

    private function __construct(Console $console)
    {
        $this->console = $console;
    }


    public function getPropertyData(): array
    {
        // $category => $name => [$shortcut, $description, $typeHint, $defaultValue, $validator]
        return [
            'Commands' => [
                'analyse' =>  ['',  'run spell-check'],
                'help' =>     ['h', 'show help'],
                'license' =>  ['',  'show license'],
            ],
            'Configuration' => [
                '--config' =>       ['c', 'configuration files', 'paths', __DIR__ . '/build/spell-checker.neon'],
                '--memoryLimit' =>  ['m', 'memory limit', null, null, [$this, 'setMemoryLimit']],
                '--use' =>          ['',  'configuration profiles to use', 'profiles'],
                '--debug' =>        ['',  'show debug info'],
                '--baseDir' =>      ['b', 'base directory for relative paths (all files)', 'path'],
            ],
            'File selection' => [
                '--filesBaseDir' => ['',  'base directory for relative paths (checked files)', 'path'],
                '--files' =>        ['f', 'files to check', 'paths'],
                '--directories' =>  ['d', 'directories to check', 'paths'],
                '--extensions' =>   ['e', 'file extensions to check', 'extensions'],
            ],
            'Dictionaries' => [
                '--dictionariesBaseDir' =>         ['', 'base directory for relative paths (dictionaries)', 'path'],
                '--dictionaryDirectories' =>       ['', 'paths to directories containing dictionaries', 'paths'],
                '--dictionaries' =>                ['D', 'dictionaries to use on all files', 'list'],
                '--dictionariesByFileName' =>      ['n', 'file name pattern -> list of dictionaries', 'map'],
                '--dictionariesByFileExtension' => ['x', 'file extension -> list of dictionaries', 'map'],
                '--dictionariesWithDiacritics' =>  ['', 'dictionaries containing words with diacritics', 'list'],
            ],
            'Other' => [
                '--localIgnores'      =>      ['', 'file name pattern -> list of locally ignored words', 'map'],
                '--checkLocalIgnores' =>      ['', 'check if all local exceptions are used'],
                '--checkDictionaryFiles' =>   ['', 'check configured dictionary file for unused word'],
                '--dictionaryFilesToCheck' => ['', 'list of user dictionaries to check for unused words', 'names'],
                '--short' =>                  ['s', 'shorter output with only file and list of words'],
                '--topWords' =>               ['t', 'output list of top misspelled words'],
                '--maxErrors' =>              ['',  'maximum number of error before check stops', 'number'],
                '--wordsParserExceptions' =>  ['', 'irregular words', 'words'],
                '--ignoreUrls' =>             ['', 'ignore all words from URL addresses'],
                '--ignoreEmails' =>           ['', 'ignore all words from email addresses'],
            ],
            'CLI output' => [
                '--noColors' => ['C', 'without colors'],
                '--noLogo' =>   ['L', 'without logo'],
                '--noOutput' => ['O', 'without visual output - only set exit code'],
            ],
        ];
    }

    private function setMemoryLimit(string $value): int
    {
        if ($match = Str::match('#^(\d+)([kMG])?$#i', $value)) {
            $this->console->writeLn(C::white(sprintf('Invalid memory limit format "%s".', $value), C::RED));
            exit(2);
        }
        if (ini_set('memory_limit', $value) === false) {
            $this->console->writeLn(C::white(sprintf('Memory limit "%s" cannot be set.', $value), C::RED));
            exit(2);
        }

        return BytesCalc::parse($value);
    }

}

