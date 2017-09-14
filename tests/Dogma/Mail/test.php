<?php

use Dogma\Email\EmailAddressAndName;
use Dogma\Email\Parse\Message;
use Dogma\Io\File;
use Tracy\Debugger;

ob_start();
require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../../../src/loader.php';

Debugger::enable(Debugger::DEVELOPMENT, __DIR__);
Debugger::$maxDepth = 5;

header('Content-Type: text/html; charset=utf-8');


$mail = new Message(__DIR__, new File(__DIR__ . '/test.eml'));

dump($mail);

dump($mail->date);

echo '<hr>';

$att = $mail->getAttachments('text/plain');
dump($att);

echo '<hr>';

dump($mail->getHeaders());

$addr = EmailAddressAndName::parseHeader($mail->getHeader('cc'));

/*
ob_end_clean();
header("Content-Type: image/jpeg");
echo $att[0]->getContent();
*/
