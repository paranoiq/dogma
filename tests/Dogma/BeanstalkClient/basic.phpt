<?php

use Tester\Assert;

require __DIR__ . '/init.php';


$jack->queue('test job 1', 2); // delay 2 seconds
$jack->queue('test job 2', 0, 5000); // priority 5000
$jack->queue('test job 3');


$stats = $jack->getQueueStats($testQueue);
Assert::same(2, $stats['current-jobs-ready']);

$job = $jack->assign();
Assert::same('test job 3', $job['body']);
$jack->finish($job['id']);

$job = $jack->assign();
Assert::same('test job 2', $job['body']);
$jack->finish($job['id']);

$job = $jack->assign();
Assert::same('test job 1', $job['body']);
$jack->release($job['id']);

$job = $jack->assign();
Assert::same('test job 1', $job['body']);
$jack->suspend($job['id']);

$job = $jack->assign(0);
Assert::same([], $job);

$jack->restore(1);

$job = $jack->assign();
Assert::same('test job 1', $job['body']);
$jack->suspend($job['id']);
