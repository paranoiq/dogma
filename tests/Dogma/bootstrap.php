<?php declare(strict_types = 1);

use Tracy\Debugger;

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../vendor/nette/tester/src/bootstrap.php';
require_once __DIR__ . '/../../vendor/dogma/dogma-dev/src/debug-client.php';

if (!empty($_SERVER['argv'])) {
    // may be running from command line, but under 'cgi-fcgi' SAPI
    header('Content-Type: text/plain');
} elseif (PHP_SAPI !== 'cli') {
    // running from browser
    Debugger::enable(Debugger::DEVELOPMENT, dirname(dirname(__DIR__)) . '/log/');
    Debugger::$strictMode = true;
}
