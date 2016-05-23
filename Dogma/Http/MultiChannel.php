<?php
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Http;

use Nette\Utils\Callback;


class MultiChannel extends \Dogma\Object
{

    /** @var \Dogma\Http\Channel[] */
    private $channels;

    /** @var string[] */
    private $cids;

    /** @var int */
    private $lastIndex = -1;

    /** @var string[][] (string $sjName => (string $cName => string $jobName)) */
    private $queue = [];

    /** @var \Dogma\Http\Response[][] (string $jobName => (string $cName => \Dogma\Http\Response $response)) */
    private $finished = [];


    /** @var \Nette\Utils\Callback */
    private $responseHandler;

    /** @var \Nette\Utils\Callback */
    private $redirectHandler;

    /** @var \Nette\Utils\Callback */
    private $errorHandler;

    /** @var \Nette\Utils\Callback */
    private $dispatch;

    /**
     * @param \Dogma\Http\Channel[]
     */
    public function __construct(array $channels)
    {
        $this->channels = $channels;

        /** @var \Dogma\Http\Channel $channel */
        foreach ($channels as $cName => $channel) {
            $this->cids[spl_object_hash($channel)] = $cName;
            $channel->setResponseHandler(new Callback($this, 'responseHandler'));
        }
    }

    public function responseHandler(Response $response, Channel $channel, string $sjName)
    {
        $cid = spl_object_hash($channel);
        $cName = $this->cids[$cid];
        $jobName = $this->queue[$sjName][$cName];
        $this->finished[$jobName][$cName] = $response;

        unset($this->queue[$sjName][$cName]);
        if (empty($this->queue[$sjName])) {
            unset($this->queue[$sjName]);
        }

        if (count($this->finished[$jobName]) == count($this->channels)) {
            $this->jobFinished($jobName);
        }
    }

    /**
     * @param string|int
     */
    private function jobFinished($jobName)
    {
        /** @var \Dogma\Http\Response $response */
        foreach ($this->finished[$jobName] as $response) {
            if ($response->getStatus()->isError()) {
                $error = true;
            }
            if ($response->getStatus()->isRedirect()) {
                $redirect = true;
            }
        }

        if ($this->errorHandler && isset($error)) {
            $this->errorHandler->invoke($this->finished[$jobName], $this);
            unset($this->finished[$jobName]);

        } elseif ($this->redirectHandler && isset($redirect)) {
            $this->redirectHandler->invoke($this->finished[$jobName], $this);
            unset($this->finished[$jobName]);

        } elseif ($this->responseHandler) {
            $this->responseHandler->invoke($this->finished[$jobName], $this);
            unset($this->finished[$jobName]);
        }
    }

    /**
     * @return \Dogma\Http\Channel[]
     */
    public function getChannels(): array
    {
        return $this->channels;
    }

    /**
     * Set callback handler for every response (even an error)
     * @param \Nette\Utils\Callback(\Dogma\Http\Response $response, \Dogma\Http\Channel $channel, string $name)
     */
    public function setResponseHandler(Callback $responseHandler)
    {
        $this->responseHandler = $responseHandler;
    }

    /**
     * Set separate callback handler for redirects. ResponseHandler will no longer handle these.
     * @param \Nette\Utils\Callback(\Dogma\Http\Response $response, \Dogma\Http\Channel $channel, string $name)
     */
    public function setRedirectHandler(Callback $redirectHadler)
    {
        $this->redirectHandler = $redirectHadler;
    }

    /**
     * Set separate callback handler for errors. ResponseHandler will no longer handle these.
     * @param \Nette\Utils\Callback(\Dogma\Http\Response $response, \Dogma\Http\Channel $channel, string $name)
     */
    public function setErrorHandler(Callback $errorHandler)
    {
        $this->errorHandler = $errorHandler;
    }

    /**
     * @param callable(mixed $data, \Dogma\Http\Channel[] $channels)
     */
    public function setDispatchFunction($function)
    {
        $this->dispatch = new Callback($function);
    }

    /**
     * Add new job to channel queue.
     * @param string|array
     * @param mixed
     * @param string|int
     * @return string|int
     */
    public function addJob($data, $context = null, $name = null)
    {
        if (is_string($name) || is_int($name)) {
            // ok
        } elseif ($name === null) {
            $name = ++$this->lastIndex;

        } else {
            throw new ChannelException('Illegal job name. Job name can be only a string or an integer.');
        }

        if ($this->dispatch) {
            $subJobs = $this->dispatch->invoke($data, $this->channels);
        } else {
            $subJobs = $this->dispatch($data);
        }
        foreach ($subJobs as $channel => $job) {
            $sjName = $this->channels[$channel]->addJob($job, $context);
            $this->queue[$sjName][$channel] = $name;
        }

        return $name;
    }

    /**
     * Add more jobs to a channel. Array indexes are job names if they are strings.
     * @param mixed[]
     * @param mixed
     */
    public function addJobs(array $jobs, $context = null)
    {
        $useKeys = array_keys($jobs) !== range(0, count($jobs) - 1);

        foreach ($jobs as $name => $data) {
            $this->addJob($data, $context, $useKeys ? $name : null);
        }
    }

    /**
     * Run a new job and wait for the response.
     * @param string|array
     * @param mixed
     * @return \Dogma\Http\Response|null
     */
    public function fetchJob($data, $context = null)
    {
        $jobs = $this->dispatch($data);
        foreach ($jobs as $channel => $job) {
            $jobs[$channel] = $this->channels[$channel]->runJob($job, $context, null);
        }
        $responses = [];
        foreach ($jobs as $channel => $sjName) {
            $responses[$channel] = $this->channels[$channel]->fetch($sjName);
        }
        return $responses;
    }

    /**
     * @param string|int
     * @return \Dogma\Http\Response[]|null
     */
    public function fetch($name = null)
    {
        if ($name !== null) {
            return $this->fetchNamedJob($name);
        }

        if (empty($this->queue) && empty($this->finished)) {
            return null;
        }

        $keys = array_keys($this->channels);
        do {
            $this->channels[$keys[0]]->read();
            foreach ($this->finished as $name => $fin) {
                if (count($fin) === count($this->channels)) {
                    unset($this->finished[$name]);
                    return $fin;
                }
            }
        } while (true);

        return null;
    }

    /**
     * @param string|int
     * @return \Dogma\Http\Response[]|null
     */
    private function fetchNamedJob($name)
    {
        if (!isset($this->queue[$name]) && !isset($this->finished[$name])) {
            throw new ChannelException(sprintf('Job named \'%s\' was not found.', $name));
        }

        if (isset($this->finished[$name]) && count($this->finished[$name]) === count($this->channels)) {
            $responses = $this->finished[$name];
            unset($this->finished[$name]);
            return $responses;
        }

        // seek subjobs
        foreach ($this->queue as $sjName => $a) {
            foreach ($a as $cName => $jobName) {
                if ($jobName === $name) {
                    $this->responseHandler($this->channels[$cName]->fetch($sjName), $this->channels[$cName], $sjName);
                }
            }
        }

        $response = $this->finished[$name];
        unset($this->finished[$name]);
        return $response;
    }

    /**
     * Wait till all jobs are finished.
     */
    public function finish()
    {
        foreach ($this->channels as $channel) {
            $channel->finish();
        }
    }

    /**
     * Check if all channels or a channel or a job are finished.
     */
    public function isFinished(): bool
    {
        foreach ($this->channels as $channel) {
            if (!$channel->isFinished()) {
                return false;
            }
        }
        return true;
    }

    public function read()
    {
        foreach ($this->channels as $channel) {
            $channel->read();
        }
    }

    /**
     * Job data dispatch function. Splits up data for sub-jobs (sub-channels). Override if needed.
     * @param string|array
     * @return array
     */
    protected function dispatch($data): array
    {
        if (is_string($data)) {
            // default - send copy to all channels
            $jobs = [];
            foreach ($this->channels as $name => $channel) {
                $jobs[$name] = $data;
            }

        } elseif (is_array($data)) {
            // default - array is indexed by channel name
            return $data;

        } else {
            throw new ChannelException('Illegal job data. Job data can be either string or array.');
        }

        return $jobs;
    }

}
