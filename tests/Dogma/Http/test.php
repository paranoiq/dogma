<?php

use Tracy\Debugger;

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../../../Dogma/loader.php';

Debugger::enable(Debugger::DEVELOPMENT, __DIR__);

header('Content-Type: text/html; charset=utf-8');

echo '<!DOCTYPE HTML><html><head><title>Dogma\\Http</title></head><body>';


use Dogma\Http;


$manager = new Http\ChannelManager;

$requestA = new Http\DownloadRequest('http://lh/vsp/dogma/tests/Dogma/Http/responder.php', __DIR__);
$requestA->setFollowRedirects(true);

$requestB = new Http\Request('http://lh/vsp/dogma/tests/Dogma/Http/responder.php');
$requestB->setFollowRedirects(true);

$manager->addChannel($channelA = new Http\Channel($manager, $requestA));
$manager->addChannel($channelB = new Http\Channel($manager, $requestB));
$manager->addChannel($channelC = new Http\Channel($manager, $requestB));
$manager->addChannel($channelD = new Http\Channel($manager, $requestB));
$manager->addChannel($channelE = new Http\Channel($manager, $requestB));
$manager->addChannel($channelF = new Http\Channel($manager, $requestB));
$manager->addChannel($channelG = new Http\Channel($manager, $requestB));
$manager->addChannel($channelH = new Http\Channel($manager, $requestB));

$channelG->setPriority(6.0);
$channelH->setPriority(12.0);

while (@$m++ < 20) {
    $channelA->addJob('?size=100&redir=3', 'A' . $m);
}

while (@$n++ < 100) {
    $channelB->addJob('?size=10000', 'B' . $n);
    $channelC->addJob('?size=10000', 'C' . $n);
    $channelD->addJob('?size=10000', 'D' . $n);
    $channelE->addJob('?size=10000', 'E' . $n);
    $channelF->addJob('?size=10000', 'F' . $n);
    $channelG->addJob('?size=10000', 'G' . $n);
    $channelH->addJob('?size=10000', 'H' . $n);
}

while ($response = $channelA->fetch()) {
    echo $response->getBody();
}

echo $response;

dump($response);
dump($manager);
