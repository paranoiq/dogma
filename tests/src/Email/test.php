<?php declare(strict_types = 1);

use Dogma\Email\EmailMessage;
use Dogma\Io\File;
use Tracy\Debugger;

ob_start();
require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../../../src/loader.php';

Debugger::enable(Debugger::DEVELOPMENT, __DIR__);
Debugger::$maxDepth = 5;

header('Content-Type: text/html; charset=utf-8');


$mail = new EmailMessage(new File(__DIR__ . '/test.eml'));

dump($mail);

dump($mail->date);

echo '<hr>';

$attributes = $mail->getAttachments('text/plain');
dump($attributes);

echo '<hr>';

dump($mail->getHeaders());

//$address = Address::parseHeader($mail->getHeader('cc'));

/*
ob_end_clean();
header("Content-Type: image/jpeg");
echo $attributes[0]->getContent();
*/
