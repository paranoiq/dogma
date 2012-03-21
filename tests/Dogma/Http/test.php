<?php

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../../../Dogma/Http/loader.php';

use Nette\Diagnostics\Debugger;

Debugger::enable(Debugger::DEVELOPMENT, __DIR__);

header("Content-Type: text/html; charset=utf-8");


use Dogma\Http;


$manager = new Http\RequestManager;

$request = new Http\Request('http://lh/vsp/dogma/tests/Dogma/Http/responder.php');
$request->setFollowRedirects(TRUE);

$channelA = $manager->createChannel(clone $request);
$channelB = $manager->createChannel($request);
$channelC = $manager->createChannel($request);
$channelD = $manager->createChannel($request);
$channelE = $manager->createChannel($request);
$channelF = $manager->createChannel($request);
$channelG = $manager->createChannel($request);
$channelH = $manager->createChannel($request);

$channelA->getRequestPrototype()->setDownloadDir(__DIR__);

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
