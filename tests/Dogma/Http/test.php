<?php

require_once __DIR__ . '/../bootstrap.php';

use Nette\Diagnostics\Debugger;

Debugger::enable(Debugger::DEVELOPMENT, __DIR__);

header("Content-Type: text/html; charset=utf-8");

echo "<!DOCTYPE HTML><html><head><title>Dogma\\Http</title></head><body>";

$dir = __DIR__ . '/../../../Dogma';

require_once $dir . '/Object/Object.php';
require_once $dir . '/types/Enum.php';
require_once $dir . '/Http/exceptions.php';
require_once $dir . '/Http/HttpCode.php';
require_once $dir . '/Http/Response.php';
require_once $dir . '/Http/FileResponse.php';
require_once $dir . '/Http/Request.php';
require_once $dir . '/Http/DownloadRequest.php';
require_once $dir . '/Http/Channel.php';
require_once $dir . '/Http/RequestManager.php';
require_once $dir . '/Http/CurlHelpers.php';


use Dogma\Http;


$manager = new Http\RequestManager;

$requestA = new Http\DownloadRequest('http://lh/vsp/dogma/tests/Dogma/Http/responder.php', __DIR__);
$requestA->setFollowRedirects(TRUE);

$requestB = new Http\Request('http://lh/vsp/dogma/tests/Dogma/Http/responder.php');
$requestB->setFollowRedirects(TRUE);

$channelA = $manager->createChannel($requestA);
$channelB = $manager->createChannel($requestB);
$channelC = $manager->createChannel($requestB);
$channelD = $manager->createChannel($requestB);
$channelE = $manager->createChannel($requestB);
$channelF = $manager->createChannel($requestB);
$channelG = $manager->createChannel($requestB);
$channelH = $manager->createChannel($requestB);

$channelG->setPriority(6.0);
$channelH->setPriority(12.0);

while (@$m++ < 20) {
    $channelA->addJob("?size=100&redir=3", "A$m");
}

while (@$n++ < 100) {
    $channelB->addJob("?size=10000", "B$n");
    $channelC->addJob("?size=10000", "C$n");
    $channelD->addJob("?size=10000", "D$n");
    $channelE->addJob("?size=10000", "E$n");
    $channelF->addJob("?size=10000", "F$n");
    $channelG->addJob("?size=10000", "G$n");
    $channelH->addJob("?size=10000", "H$n");
}

while ($response = $channelA->fetch()) {
    echo $response->getBody();
}

echo $response;

dump($response);
dump($manager);
