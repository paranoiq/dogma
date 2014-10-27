<?php

use Dogma\Queue\BeanstalkClient;
use Tester\Environment;

require __DIR__ . '/../bootstrap.php';

$jack = new BeanstalkClient;

echo '<pre><code>';

$testQueue = 'Jack-Beanstalk-Client-Testing-Queue';

try {
    $jack->watchQueue($testQueue);
    $jack->ignoreQueue('default');

    $jack->selectQueue($testQueue);

    // cleaning
    while ($job = $jack->assign(0)) {
        $jack->delete($job['id']);
    }
} catch (\Dogma\Queue\BeanstalkException $e) {
    Environment::skip();
    exit;
}
