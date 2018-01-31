<?php
// spell-check-ignore: vsp lh

namespace Dogma\Test\Http;

use Dogma\Http\Channel\Channel;
use Dogma\Http\Channel\ChannelManager;
use Dogma\Http\DownloadRequest;
use Dogma\Http\Request;
use Tracy\Debugger;

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../../../src/loader.php';

Debugger::enable(Debugger::DEVELOPMENT, __DIR__);

header('Content-Type: text/html; charset=utf-8');

echo '<!DOCTYPE HTML><html><head><title>Dogma\\Http</title></head><body>';


$manager = new ChannelManager();

$requestA = new DownloadRequest('http://lh/vsp/dogma/tests/Dogma/Http/responder.php');
$requestA->setFollowRedirects(true);

$requestB = new Request('http://lh/vsp/dogma/tests/Dogma/Http/responder.php');
$requestB->setFollowRedirects(true);

$manager->addChannel($channelA = new Channel($requestA, $manager));
$manager->addChannel($channelB = new Channel($requestB, $manager));
$manager->addChannel($channelC = new Channel($requestB, $manager));
$manager->addChannel($channelD = new Channel($requestB, $manager));
$manager->addChannel($channelE = new Channel($requestB, $manager));
$manager->addChannel($channelF = new Channel($requestB, $manager));
$manager->addChannel($channelG = new Channel($requestB, $manager));
$manager->addChannel($channelH = new Channel($requestB, $manager));

$channelG->setPriority(6);
$channelH->setPriority(12);

$m = 0;
while ($m++ < 20) {
    $channelA->addJob('?size=100&redir=3', 'A' . $m);
}

$n = 0;
while ($n++ < 100) {
    $channelB->addJob('?size=10000', 'B' . $n);
    $channelC->addJob('?size=10000', 'C' . $n);
    $channelD->addJob('?size=10000', 'D' . $n);
    $channelE->addJob('?size=10000', 'E' . $n);
    $channelF->addJob('?size=10000', 'F' . $n);
    $channelG->addJob('?size=10000', 'G' . $n);
    $channelH->addJob('?size=10000', 'H' . $n);
}

$response = null;
while ($response = $channelA->fetch()) {
    echo $response->getBody();
}

dump($response);
dump($manager);
