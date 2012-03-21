<?php

require __DIR__ . "/../../Test/Assert.php";
require __DIR__ . "/../../Test/TestCase.php";
require __DIR__ . "/../../../BeanstalkClient/BeanstalkClient.php";

$jack = new Jack\BeanstalkClient;

echo "<pre><code>";
//var_export($jack);
//echo "\n";

$testQueue = 'Jack-Beanstalk-Client-Testing-Queue';

$jack->watchQueue($testQueue);
$jack->ignoreQueue('default');

$jack->selectQueue($testQueue);

// cleaning
while ($job = $jack->assign(0)) {
    $jack->delete($job['id']);
}

