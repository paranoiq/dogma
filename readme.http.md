Http
====

Http Request Manager allows you to download/upload many requests in multiple channels in parallel.
New jobs can be added on the fly, while the other are fetched.
Responses can be fetched synchronously, or asynchronously as they come, or with a callback function.
Responses can even be fetched by the job name.


initialisation:
    $manager = new ChannelManager;
    $channel = $manager->createChannel("http://example.com");


asynchronous response handling:
    $channel->setHandler('my_callcack_function');
    
    $channel->addJobs($jobs);
    
    $channel->finish();


batch response handling:
    $channel->addJobs($jobs);
        
    while (!$channel->isFinished()) {
        $response = $channel->fetch();
    }


synchronous request/response handling:
    while (...) {
        $response = $channel->fetchJob($job);
    }


fetching by job name:
    $channel->addJobs($jobs);
    $name = $channel->addJob($data);
    
    $channel->fetch($name); // fetch the last one first!
    ...

using callback handlers:
    $channel->addResponseHandler($cb); // called when downloaded
    $channel->addErrorHandler($cb);
    $channel->addRedirectHandler($cb);
    
    
    
    

