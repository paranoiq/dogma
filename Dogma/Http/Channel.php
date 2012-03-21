<?php

namespace Dogma\Http;

use Nette\Callback;



/**
 * Proxy object to ease work with channels in RequestManager.
 *
 * @property-read $request
 * @property-read $callback
 * @property-read $priority
 * @property-read $threadLimit
 * @property-read $queued
 * @property-read $running
 * @property-read $finished
 * @property-read $fetched
 */
class Channel extends \Dogma\Object {

    /** @var RequestManager */
    private $manager;
    
    /** @var Request */
    private $request;

    
    /**
     * @param RequestManager
     * @param Request
     */
    public function  __construct(RequestManager $manager, Request $request) {
        $this->manager = $manager;
        $this->request = $request;
    }
    
    
    /**
     * @return Request
     */
    public function getRequestPrototype() {
        return $this->request;
    }
    

    /**
     * Add new job to channel. String for GET. Array for POST.
     * @param string|array
     * @param string
     * @return self
     */
    public function addJob($job, $name = 0) {
        $this->manager->addJob($this, $job, $name);

        return $this;
    }


    /**
     * Add more jobs to a channel. Array indexes are job names if they are strings.
     * @param array
     * @return self
     */
    public function addJobs(array $jobs) {
        $this->manager->addJobs($this, $jobs);

        return $this;
    }


    /**
     * @param float
     * @return self
     */
    public function setPriority($priority) {
        $this->manager->setPriority($this, $priority);

        return $this;
    }


    /**
     * @param int
     * @return self
     */
    public function setThreadLimit($threads) {
        $this->manager->setThreadLimit($this, abs((int)$threads));

        return $this;
    }


    /**
     * @return array
     */
    public function getInfo() {
        return $this->manager->getChannelInfo($this);
    }
    
    
    /**
     * Check if a job or the whole channel finished.
     * @param string
     * @return bool
     */
    public function isFinished($name = NULL) {
        return $this->manager->isFinished($this, $name);
    }


    /**
     * @param string
     * @return Response|bool
     */
    public function fetch($name = NULL) {
        return $this->manager->fetch($this, $name);
    }

    

    public function &__get($name) {
        $info = $this->manager->getChannelInfo($this);
        if (isset($info[$name])) {
            $val = $info[$name];
            return $val;
        }

        return parent::__get($name);
    }

}
