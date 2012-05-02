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
     * Set callback handlers on job finish.
     * @param callable
     * @param callable
     * @return self
     */
    public function setHandlers(Callback $onSuccess, Callback $onFailure) {
        $this->manager->setHandlers($this, $onSuccess, $onFailure);

        return $this;
    }
    


    /**
     * Add new job to channel queue.
     * @param string|array
     * @param string
     * @param mixed Request context
     * @return string|int
     */
    public function addJob($data, $name = 0, $context = NULL) {
        return $this->manager->addJob($this, $data, $name, $context);
    }

    
    /**
     * Run a new job and wait for the response.
     * @param string|array
     * @param string|int
     * @return Response|NULL
     */
    public function runJob($data, $name = 0) {
        $name = $this->manager->addJob($this, $data, $name);
        return $this->manager->fetch($this, $name);
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

    
    /**
     * Stop channel (do not start any jobs).
     * @return self
     */
    public function stop() {
        $this->manager->stopChannel($this);
        
        return $this;
    }
    
    
    /**
     * Check if channel is stopped.
     * @return bool
     */
    public function isStopped() {
        return $this->manager->isStopped($this);
    }
    
    
    /**
     * Restore function of channel.
     * @return self 
     */
    public function resume() {
        $this->manager->resumeChannel($this);
        
        return $this;
    }


    /**
     * @return self
     */
    public function read() {
        $this->manager->read();

        return $this;
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
