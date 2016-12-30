<?php
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Http;

/**
 * HTTP channel for multiple similar requests.
 *
 * @todo: vyřešit možná nekonečné smyčky při čekání na odpověď
 * @todo: vyřešit fetch() ze zapausovaného kanálu
 */
class Channel
{
    use \Dogma\StrictBehaviorMixin;

    /** @var \Dogma\Http\ChannelManager */
    protected $manager;

    /** @var \Dogma\Http\Request */
    private $request;

    /** @var int */
    private $priority = 1;

    /** @var int */
    private $threadLimit = 10;

    /** @var int */
    private $lastIndex = 0;

    /** @var bool */
    private $initiated = false;

    /** @var bool */
    private $stopped = false;

    /** @var bool|int */
    private $paused = false;


    /** @var array */
    private $queue = [];

    /** @var array */
    private $running = [];

    /** @var \Dogma\Http\Response[] */
    private $finished = [];

    /** @var array */
    private $contexts = [];



    /** @var callable */
    private $responseHandler;

    /** @var callable */
    private $redirectHandler;

    /** @var callable */
    private $errorHandler;

    public function __construct(ChannelManager $manager, Request $request)
    {
        $this->manager = $manager;
        $this->request = $request;
    }

    public function getRequestPrototype(): Request
    {
        return $this->request;
    }

    /**
     * Set callback handler for every response (even an error)
     * @param callable(\Dogma\Http\Response $response, \Dogma\Http\Channel $channel, string $name)
     */
    public function setResponseHandler(callable $responseHandler)
    {
        $this->responseHandler = $responseHandler;
    }

    /**
     * Set separate callback handler for redirects. ResponseHandler will no longer handle these.
     * @param callable(\Dogma\Http\Response $response, \Dogma\Http\Channel $channel, string $name)
     */
    public function setRedirectHandler(callable $redirectHadler)
    {
        $this->redirectHandler = $redirectHadler;
    }

    /**
     * Set separate callback handler for errors. ResponseHandler will no longer handle these.
     * @param callable(\Dogma\Http\Response $response, \Dogma\Http\Channel $channel, string $name)
     */
    public function setErrorHandler(callable $errorHandler)
    {
        $this->errorHandler = $errorHandler;
    }

    public function setPriority(int $priority)
    {
        $this->priority = abs($priority);
    }

    public function getPriority(): int
    {
        return $this->priority;
    }

    public function setThreadLimit(int $threads)
    {
        $this->threadLimit = abs($threads);
    }

    public function getThreadLimit(): int
    {
        return $this->threadLimit;
    }

    // jobs ------------------------------------------------------------------------------------------------------------

    /**
     * Run a new job immediately and wait for the response.
     * @param string|string[]
     * @param mixed
     * @return \Dogma\Http\Response|null
     */
    public function fetchJob($data, $context = null)
    {
        $name = $this->addJob($data, $context, null, true);

        return $this->fetch($name);
    }

    /**
     * Run a new job immediately. Don't wait for response.
     * @param string|string[]
     * @param mixed
     * @param string|int
     * @return string|int
     */
    public function runJob($data, $context = null, $name = null)
    {
        return $this->addJob($data, $context, $name, true);
    }

    /**
     * Add new job to channel queue.
     * @param string|string[]
     * @param mixed
     * @param string|int
     * @return string|int
     */
    public function addJob($data, $context = null, $name = null, $forceStart = false)
    {
        if (!is_string($data) && !is_array($data)) {
            throw new ChannelException('Illegal job data. Job data can be either string or array.');
        }

        if (is_string($name) || is_int($name)) {
            $this->queue[$name] = $data;

        } elseif ($name === null) {
            $name = ++$this->lastIndex;
            $this->queue[$name] = $data;

        } else {
            throw new ChannelException('Illegal job name. Job name can be only a string or an integer.');
        }

        if (isset($context)) {
            $this->contexts[$name] = $context;
        }

        if ($forceStart) {
            $this->startJob($name);
        } else {
            $this->manager->startJobs();
        }

        return $name;
    }

    /**
     * Add more jobs to a channel. Array indexes are job names if they are strings.
     * @param string[]|string[][]
     * @param mixed
     */
    public function addJobs(array $jobs, $context = null)
    {
        $useKeys = array_keys($jobs) !== range(0, count($jobs) - 1);

        foreach ($jobs as $name => $data) {
            $this->addJob($data, $context, $useKeys ? $name : null);
        }
    }

    public function getRunningJobCount(): int
    {
        return count($this->running);
    }

    /**
     * Decide if channel can start a job.
     */
    public function canStartJob(): bool
    {
        if (empty($this->queue)) {
            return false;
        }
        if ($this->stopped) {
            return false;
        }
        if ($this->isPaused()) {
            return false;
        }
        if (!empty($this->running) && count($this->running) >= $this->threadLimit) {
            return false;
        }

        return true;
    }

    /**
     * Start a request in CURL. Called by ChannelManager
     * @internal
     *
     * @param string|int|null
     */
    public function startJob($name = null)
    {
        if (!$this->canStartJob()) {
            return null;
        }

        if ($name === null) {
            $name = array_keys($this->queue);
            $name = $name[0];
        }

        if (!$this->initiated) {
            $this->request->init();
            $this->initiated = true;
        }

        $request = clone $this->request;
        $request->setData($this->queue[$name]);

        if (!empty($this->contexts[$name])) {
            $request->setContext($this->contexts[$name]);
            unset($this->contexts[$name]);
        }

        $request->prepare('', $name);
        $handler = $request->getHandler();
        if ($err = curl_multi_add_handle($this->manager->getHandler(), $handler)) {
            throw new ChannelException('CURL error when adding a job: ' . CurlHelpers::getCurlMultiErrorName($err), $err);
        }

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
     * @param \Dogma\Http\Request
     */
    public function jobFinished($name, array $minfo, Request $request)
    {
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
     * @param string|int
     * @return \Dogma\Http\Response|null
     */
    public function fetch($name = null)
    {
        if ($name !== null) {
            return $this->fetchByName($name);
        }

        if (!empty($this->finished)) {
            return array_shift($this->finished);
        }

        // start one job immediately
        if (empty($this->running)) {
            if (empty($this->queue)) {
                return null;
            }

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
     * @return \Dogma\Http\Response|null
     */
    private function fetchByName($name)
    {
        if (!isset($this->queue[$name]) && !isset($this->running[$name]) && !isset($this->finished[$name])) {
            throw new ChannelException(sprintf('Job named \'%s\' was not found.', $name));
        }

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
     * @param string|int
     * @return bool
     */
    public function isFinished($name = null): bool
    {
        if ($name === null) {
            return empty($this->running) && empty($this->queue);
        } else {
            return isset($this->finished[$name]);
        }
    }

    /**
     * Wait till all jobs are finished.
     */
    public function finish()
    {
        while (!$this->isFinished()) {
            $this->manager->read();
        }
    }

    public function stop()
    {
        $this->stopped = true;
    }

    public function isStopped(): bool
    {
        return $this->stopped;
    }

    public function pause(int $seconds = 0)
    {
        if ($seconds) {
            $this->paused = time() + $seconds;
        } else {
            $this->paused = true;
        }
    }

    public function isPaused(): bool
    {
        if (is_int($this->paused) && $this->paused <= time()) {
            $this->paused = false;
        }
        return (bool) $this->paused;
    }

    public function resume()
    {
        $this->stopped = false;
        $this->paused = false;
    }

    public function read()
    {
        $this->manager->read();
    }

}
