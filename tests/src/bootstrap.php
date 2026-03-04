<?php declare(strict_types = 1);

// phpcs:disable SlevomatCodingStandard.Variables.DisallowSuperGlobalVariable

require_once __DIR__ . '/../../vendor/dogma/dogma-debug/client.php';
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../vendor/nette/tester/src/bootstrap.php';

if (!empty($_SERVER['argv'])) {
    // may be running from command line, but under 'cgi-fcgi' SAPI
    header('Content-Type: text/plain');
} elseif (PHP_SAPI !== 'cli') {
    // running from browser
    header('Content-Type: text/html');
}
