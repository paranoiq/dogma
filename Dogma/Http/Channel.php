<?php

namespace Dogma\Http;

use Nette\Callback;



/**
 * HTTP channel for multiple similar requests.
 * 
 * @todo: vyřešit možná nekonečné smyčky při čekání na odpověď
 * @todo: vyřešit fetch() ze zapausovaného kanálu
 */
class Channel extends \Dogma\Object {

    /** @var ChannelManager */
    protected $manager;
    
    /** @var Request */
    private $request;

    /** @var int */
    private $priority = 1;
    
    /** @var int */
    private $threadLimit = 10;
    
    /** @var int */
    private $lastIndex = 0;
    
    /** @var bool */
    private $initiated = FALSE;
    
    /** @var bool */
    private $stopped = FALSE;
    
    /** @var bool|int */
    private $paused = FALSE;
    
    
    /** @var array */
    private $queue = array();
    
    /** @var array */
    private $running = array();
    
    /** @var Response[] */
    private $finished = array();
    
    /** @var array */
    private $contexts = array();
    
    
    
    /** @var \Nette\Callback */
    private $responseHandler;

    /** @var \Nette\Callback */
    private $redirectHandler;

    /** @var \Nette\Callback */
    private $errorHandler;
    
    
    
    /**
     * @param ChannelManager
     * @param Request
     */
    public function  __construct(ChannelManager $manager, Request $request) {
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
     * Set callback handler for every response (even an error)
     * @param Callback(@param Response, @param Channel, @param string $name)
     * @return self
     */
    public function setResponseHandler(Callback $responseHandler) {
        $this->responseHandler = $responseHandler;

        return $this;
    }


    /**
     * Set separate callback handler for redirects. ResponseHandler will no longer handle these.
     * @param Callback(@param Response, @param Channel, @param string $name)
     * @return self
     */
    public function setRedirectHandler(Callback $redirectHadler) {
        $this->redirectHandler = $redirectHadler;

        return $this;
    }


    /**
     * Set separate callback handler for errors. ResponseHandler will no longer handle these.
     * @param Callback(@param Response, @param Channel, @param string $name)
     * @return self
     */
    public function setErrorHandler(Callback $errorHandler) {
        $this->errorHandler = $errorHandler;

        return $this;
    }


    /**
     * @param int
     * @return self
     */
    public function setPriority($priority) {
        $this->priority = abs((int) $priority);

        return $this;
    }


    /**
     * @return int
     */
    public function getPriority() {
        return $this->priority;
    }


    /**
     * @param int
     * @return self
     */
    public function setThreadLimit($threads) {
        $this->threadLimit = abs((int) $threads);

        return $this;
    }


    /**
     * @return int
     */
    public function getThreadLimit() {
        return $this->threadLimit;
    }
    
    
    // jobs ------------------------------------------------------------------------------------------------------------
    

    /**
     * Run a new job immediately and wait for the response.
     * @param string|array
     * @param mixed
     * @return Response|NULL
     */
    public function fetchJob($data, $context = NULL) {
        $name = $this->addJob($data, $context, NULL, TRUE);
        
        return $this->fetch($name);
    }


    /**
     * Run a new job immediatelly. Don't wait for response.
     * @param string|array
     * @param mixed
     * @param string|int
     * @return string|int
     */
    public function runJob($data, $context = NULL, $name = NULL) {
        return $this->addJob($data, $context, $name, TRUE);
    }
    

    /**
     * Add new job to channel queue.
     * @param string|array
     * @param mixed
     * @param string|int
     * @return string|int
     */
    public function addJob($data, $context = NULL, $name = NULL, $forceStart = FALSE) {
        if (!is_string($data) && !is_array($data))
            throw new ChannelException('Illegal job data. Job data can be either string or array.');

        if (is_string($name) || is_int($name)) {
            $this->queue[$name] = $data;

        } elseif ($name === NULL) {
            $name = ++$this->lastIndex;
            $this->queue[$name] = $data;

        } else {
            throw new ChannelException(
                'Illegal job name. Job name can be only a string or an integer.');
        }

        if (isset($context))
            $this->contexts[$name] = $context;

        if ($forceStart) {
            $this->startJob($name);
        } else {
            $this->manager->startJobs();
        }

        return $name;
    }


    /**
     * Add more jobs to a channel. Array indexes are job names if they are strings.
     * @param array
     * @param mixed
     * @return self
     */
    public function addJobs(array $jobs, $context = NULL) {
        $useKeys = array_keys($jobs) !== range(0, count($jobs) - 1);
        
        foreach ($jobs as $name => $data) {
            $this->addJob($data, $context, $useKeys ? $name : NULL);
        }

        return $this;
    }
    
    
    /** 
     * @return int
     */
    public function getRunningJobCount() {
        return count($this->running);
    }

    
    /**
     * Decide if channel can start a job.
     * @param string
     * @return bool
     */
    public function canStartJob() {
        if (empty($this->queue)) return FALSE;
        if ($this->stopped) return FALSE;
        if ($this->isPaused()) return FALSE;
        if (!empty($this->running) && count($this->running) >= $this->threadLimit) return FALSE;

        return TRUE;
    }


    /**
     * Start a request in CURL. Called by ChannelManager
     * @internal
     *
     * @param int
     * @return array
     */
    public function startJob($name = NULL) {
        if (!$this->canStartJob()) return NULL;

        if ($name === NULL) {
            $name = array_keys($this->queue);
            $name = $name[0];
        }

        if (!$this->initiated) {
            $this->request->init();
            $this->initiated = TRUE;
        }

        $request = clone $this->request;
        $request->setData($this->queue[$name]);

        if (!empty($this->contexts[$name])) {
            $request->setContext($this->contexts[$name]);
            unset($this->contexts[$name]);
        }

        $request->prepare('', $name);
        $handler = $request->getHandler();
        if ($err = curl_multi_add_handle($this->manager->getHandler(), $handler))
            throw new ChannelException("CURL error when adding a job: "
                . CurlHelpers::getCurlMultiErrorName($err), $err);

        $this->running[$name] = $this->queue[$name];
        unset($this->queue[$name]);
        $this->manager->jobStarted($handler, $this, $name, $request);
    }
    
    
    // -----------------------------------------------------------------------------------------------------------------


    /**
     * Called by ChannelManager.
     * @internal
     *
     * @param string|int
     * @param array
     * @param Request
     */
    public function jobFinished($name, $minfo, Request $request) {
        unset($this->running[$name]);
        $data = curl_multi_getcontent($minfo['handle']);

        $response = $request->createResponse($data, $minfo['result'], $name);
        $this->finished[$name] = $response;

        if ($this->errorHandler && $response->getStatus()->isError()) {
            $this->errorHandler->invoke($response, $this, $name);
            unset($this->finished[$name]);

        } elseif ($this->redirectHandler && $response->getStatus()->isRedirect()) {
            $this->redirectHandler->invoke($response, $this, $name);
            unset($this->finished[$name]);

        } elseif ($this->responseHandler) {
            $this->responseHandler->invoke($response, $this, $name);
            unset($this->finished[$name]);
        }
    }
    
    
    /**
     * @param string
     * @return Response|NULL
     */
    public function fetch($name = NULL) {
        if ($name !== NULL) 
            return $this->fetchByName($name);

        if (!empty($this->finished)) {
            return array_shift($this->finished);
        }

        // start one job immediately
        if (empty($this->running)) {
            if (empty($this->queue))
                return NULL;

            $this->startJob();
            $this->manager->read();
        }

        // potentially endless loop, if something goes wrong (allways set request timeouts!)
        /// add timeout or retries
        while (empty($this->finished)) {
            $this->manager->read();
        }

        return array_shift($this->finished);
    }
    
    
    /**
     * @param string|int
     * @return Response|NULL
     */
    private function fetchByName($name) {
        if (!isset($this->queue[$name]) && !isset($this->running[$name]) && !isset($this->finished[$name]))
            throw new ChannelException("Job named '$name' was not found.");

        if (isset($this->finished[$name])) {
            $response = $this->finished[$name];
            unset($this->finished[$name]);
            return $response;
        }
        
        // start job immediately
        if (isset($this->queue[$name])) {
            $this->startJob($name);
            $this->manager->read();
        }

        // potentially endless loop, if something goes wrong (allways set request timeouts!)
        /// add timeout or retries
        while (!isset($this->finished[$name])) {
            $this->manager->read();
        }
        
        $response = $this->finished[$name];
        unset($this->finished[$name]);
        return $response;
    }
    
    
    /**
     * Check if all channels or a channel or a job are finished.
     * @param Channel
     * @param string
     * @return bool
     */
    public function isFinished($name = NULL) {
        if ($name === NULL) {
            return empty($this->running) && empty($this->queue);
        } else {
            return isset($this->finished[$name]);
        }
    }

    
    /**
     * Wait till all jobs are finished.
     * @return self
     */
    public function finish() {
        while (!$this->isFinished()) {
            $this->manager->read();
        }
        
        return $this;
    }

    
    public function stop() {
        $this->stopped = TRUE;
    }


    /**
     * @return bool
     */
    public function isStopped() {
        return $this->stopped;
    }
    
    
    public function pause($seconds = 0) {
        if ($seconds) {
            $this->paused = time() + $seconds;
        } else {
            $this->paused = TRUE;
        }
    }
    
    
    /**
     * @return bool
     */
    public function isPaused() {
        if (is_int($this->paused) && $this->paused <= time()) {
            $this->paused = FALSE;
        }
        return (bool) $this->paused;
    }
    
    
    public function resume() {
        $this->stopped = FALSE;
        $this->paused = FALSE;
    }
    

    /**
     * @return self
     */
    public function read() {
        $this->manager->read();

        return $this;
    }
    
}
