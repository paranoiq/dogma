<?php
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Http;

use Nette\Callback;


class MultiChannel extends \Dogma\Object {

    /** @var Channel[] */
    private $channels;

    /** @var string[] */
    private $cids;

    /** @var int */
    private $lastIndex = -1;

    /** @var string[][] array($sjName: ($cName: $jobName)) */
    private $queue = array();

    /** @var Response[][] array($jobName: ($cName: $response)) */
    private $finished = array();


    /** @var \Nette\Callback */
    private $responseHandler;

    /** @var \Nette\Callback */
    private $redirectHandler;

    /** @var \Nette\Callback */
    private $errorHandler;

    /** @var \Nette\Callback */
    private $dispatch;


    /**
     * @param Channel[]
     */
    public function __construct(array $channels) {
        $this->channels = $channels;

        foreach ($channels as $cName => $channel) {
            $this->cids[spl_object_hash($channel)] = $cName;
            $channel->setResponseHandler(new Callback($this, 'responseHandler'));
        }
    }


    /**
     * @param Response
     * @param Channel
     * @param string
     */
    public function responseHandler(Response $response, Channel $channel, $sjName) {
        $cid = spl_object_hash($channel);
        $cName = $this->cids[$cid];
        $jobName = $this->queue[$sjName][$cName];
        $this->finished[$jobName][$cName] = $response;

        unset($this->queue[$sjName][$cName]);
        if (empty($this->queue[$sjName]))
            unset($this->queue[$sjName]);

        if (count($this->finished[$jobName]) == count($this->channels)) {
            $this->jobFinished($jobName);
        }
    }


    /**
     * @param string|int
     */
    private function jobFinished($jobName) {
        foreach ($this->finished[$jobName] as $response) {
            if ($response->getStatus()->isError()) $error = true;
            if ($response->getStatus()->isRedirect()) $redirect = true;
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
     * @return Channel[]
     */
    public function getChannels() {
        return $this->channels;
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
     * @param callable(mixed $data, Channel[] $channels)
     * @return self
     */
    public function setDispatchFunction($function) {
        $this->dispatch = new Callback($function);

        return $this;
    }


    /**
     * Add new job to channel queue.
     * @param string|array
     * @param string
     * @param mixed Request context
     * @return string|int
     */
    public function addJob($data, $context = null, $name = null) {
        if (is_string($name) || is_int($name)) {
            // ok
        } elseif ($name === null) {
            $name = ++$this->lastIndex;

        } else {
            throw new ChannelException(
                'Illegal job name. Job name can be only a string or an integer.');
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
     * @param array
     * @param mixed
     * @return self
     */
    public function addJobs(array $jobs, $context = null) {
        $useKeys = array_keys($jobs) !== range(0, count($jobs) - 1);

        foreach ($jobs as $name => $data) {
            $this->addJob($data, $context, $useKeys ? $name : null);
        }

        return $this;
    }


    /**
     * Run a new job and wait for the response.
     * @param string|array
     * @param mixed
     * @return Response|null
     */
    public function fetchJob($data, $context = null) {
        $jobs = $this->dispatch($data);
        foreach ($jobs as $channel => $job) {
            $jobs[$channel] = $this->channels[$channel]->runJob($job, $context, null);
        }
        $responses = array();
        foreach ($jobs as $channel => $sjName) {
            $responses[$channel] = $this->channels[$channel]->fetch($sjName);
        }
        return $responses;
    }


    /**
     * @param string
     * @return Response[]|null
     */
    public function fetch($name = null) {
        if ($name !== null)
            return $this->fetchNamedJob($name);

        if (empty($this->queue) && empty($this->finished))
            return null;

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
     * @return Response[]|null
     */
    private function fetchNamedJob($name) {
        if (!isset($this->queue[$name]) && !isset($this->finished[$name]))
            throw new ChannelException("Job named '$name' was not found.");

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
     * @return self
     */
    public function finish() {
        foreach ($this->channels as $channel) {
            $channel->finish();
        }

        return $this;
    }


    /**
     * Check if all channels or a channel or a job are finished.
     * @param Channel
     * @return bool
     */
    public function isFinished() {
        foreach ($this->channels as $channel) {
            if (!$channel->isFinished()) return false;
        }

        return true;
    }


    /**
     * @return self
     */
    public function read() {
        foreach ($this->channels as $channel) {
            $channel->read();
            return $this;
        }

        return $this;
    }


    /**
     * Job data dispatch function. Splits up data for sub-jobs (sub-channels). Override if needed.
     * @param string|array
     * @return array
     */
    protected function dispatch($data) {
        if (is_string($data)) {
            // default - send copy to all channels
            $jobs = array();
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
