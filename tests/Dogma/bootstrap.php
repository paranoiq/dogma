<?php declare(strict_types = 1);

use Tracy\Debugger;

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../vendor/nette/tester/Tester/bootstrap.php';

// may be running from command line, but under 'cgi-fcgi' SAPI
if (!empty($_SERVER['argv'])) {
    header('Content-Type: text/plain');
// running from browser
} elseif (PHP_SAPI !== 'cli') {
    Debugger::enable(Debugger::DEVELOPMENT, dirname(dirname(__DIR__)) . '/logs/');
}
