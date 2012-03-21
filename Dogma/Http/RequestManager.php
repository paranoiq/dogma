<?php

namespace Dogma\Http;

use Nette\Callback;


class RequestManager extends \Dogma\Object {

    /**
     * List of active channels.
     *
     * $cid: [
     *      request: $request,
     *      callback: $callback,
     *      priority: $priority,
     *      threadLimit: -1,
     *      lastIndex: -1,
     *      queued: 0,
     *      running: 0,
     *      finished: 0,
     *      fetched: 0
     * ]
     *
     * @var Request[]
     */
    private $channels = array();

    /** @var float sum of priorities of all channels */
    private $sumPriorities = 0.0;

    /**
     * Waiting jobs.
     * @var array ($cid ($name: $job))
     */
    private $queue = array();

    /**
     * Running jobs by channels and names.
     * @var array ($cid: ($name: 1))
     */
    private $running = array();

    /**
     * Running jobs by resource id.
     * @var array ($resId: ($cid, $name, $request))
     */
    private $resources = array();

    /**
     * Finished jobs.
     * @var array ($cid: ($name: ($minfo, $request)))
     */
    private $finished = array();



    /** @var resource */
    private $handler;

    /** @var int maximum threads for all channles */
    private $threadLimit = 20;

    
    
    /**
     * @param string $tempDir temporary files directory for storing results
     */
    public function __construct() {
        if (!$this->handler = curl_multi_init())
            throw new RequestManagerException("Cannot initialize CURL multi-request.");
    }


    public function __destruct() {
        if ($this->handler) curl_multi_close($this->handler);
    }


    /**
     * Set maximum of request to run paralelly.
     * @param int
     */
    public function setTotalThreadLimit($threads) {
        $this->threadLimit = abs($threads);
    }


    // managing channels -----------------------------------------------------------------------------------------------


    /**
     * @param Request
     * @param Callback
     * @param float
     * @return self
     */
    public function addRequest(Request $request, Callback $callback = NULL, $priority = 1.0) {
        $channel = new Channel($this, $request);
        $cid = spl_object_hash($channel);

        $this->channels[$cid] = array(
            'request' => $request,
            'callback' => $callback,
            'priority' => $priority,
            'threadLimit' => -1,
            'lastIndex' => -1,
            'queued' => 0,
            'running' => 0,
            'finished' => 0,
            'fetched' => 0
        );
        $this->sumPriorities += $priority;

        return $channel;
    }


    /**
     * @param Request
     * @param Callback
     * @param float
     * @param int
     * @return Channel
     */
    public function createChannel(Request $request, Callback $callback = NULL, $priority = 1.0, $threadLimit = 10) {
        $channel = new Channel($this, $request);
        $cid = spl_object_hash($channel);
        
        $this->channels[$cid] = array(
            'request' => $request,
            'callback' => $callback,
            'priority' => $priority,
            'threadLimit' => abs((int) $threadLimit),
            'lastIndex' => -1,
            'queued' => 0,
            'running' => 0,
            'finished' => 0,
            'fetched' => 0
        );
        $this->sumPriorities += $priority;

        return $channel;
    }


    /**
     * @param RequestChannel
     * @return string
     */
    private function findChannel($channel) {
        $cid = spl_object_hash($channel);
        if (!isset($this->channels[$cid]))
            throw new RequestManagerException("Channel was not found!");

        return $cid;
    }


    /**
     * Remove channel from RequestManager.
     * @param Channel
     * @param bool
     * @return self
     */
    public function removeChannel(Channel $channel, $throwAwayResults = FALSE) {
        $cid = $this->findChannel($channel);

        $this->readResults(); // to empty the $running

        if (!empty($this->running[$cid]))
            throw new RequestManagerException("Cannot remove channel with running jobs!");

        if (!empty($this->finished[$cid]) && !$throwAwayResults)
            throw new RequestManagerException("Cannot remove channel while results are not fetched!");

        $this->removeChannelInt($cid);

        return $this;
    }


    /**
     * Remove channel internally.
     * @param string
     */
    private function removeChannelInt($cid) {
        $this->sumPriorities -= $this->channels[$cid]['priority'];
        unset($this->channels[$cid]);
        unset($this->queue[$cid]);
        unset($this->running[$cid]);
        unset($this->finished[$cid]);
    }



    /**
     * Add new job to a channel. String for GET. Array for POST.
     * @param Channel
     * @param string|array
     * @param string
     * @return self
     */
    public function addJob(Channel $channel, $job, $name = '') {
        $cid = $this->findChannel($channel);

        $this->addJobInt($cid, $job, $name);
        $this->startJobs();

        return $this;
    }


    /**
     * Add more jobs to a channel. Array indexes are job names if they are strings.
     * @param Channel
     * @param array
     * @return self
     */
    public function addJobs(Channel $channel, array $jobs) {
        $cid = $this->findChannel($channel);

        foreach ($jobs as $name => $job) {
            $this->addJobInt($cid, $job, $name);
        }
        $this->startJobs();

        return $this;
    }


    /**
     * Add a job internally.
     * @param string
     * @param string|array
     * @param string|int
     */
    private function addJobInt($cid, $job, $name) {
        if (!is_string($job) && !is_array($job))
            throw new RequestManagerException('Illegal job data. Job data can be either string (for GET/HEAD requests) or array (for POST requests).');
        
        if (is_string($name)) {
            $this->queue[$cid][$name] = $job;

        } elseif (is_int($name)) {
            $name = ++$this->channels[$cid]['lastIndex'];
            $this->queue[$cid][$name] = $job;
            
        } else {
            throw new RequestManagerException('Illegal job name. Job name can be only a string or an integer.');
        }

        $this->channels[$cid]['queued']++;
    }


    /**
     * @param Channel
     * @param float
     * @return self
     */
    public function setPriority(Channel $channel, $priority) {
        $cid = $this->findChannel($channel);

        $priority = abs((float) $priority);

        $this->channels[$cid]['priority'] = $priority;
        $this->sumPriorities += $priority;

        return $this;
    }


    /**
     * @param Channel
     * @param int
     * @return self
     */
    public function setThreadLimit(Channel $channel, $threads) {
        $cid = $this->findChannel($channel);

        $this->channels[$cid]['threadLimit'] = abs((int) $threads);

        return $this;
    }


    /**
     * @param Channel
     * @return array
     */
    public function getChannelInfo(Channel $channel) {
        $cid = $this->findChannel($channel);
        
        return $this->channels[$cid];
    }


    // fetching results ------------------------------------------------------------------------------------------------


    /**
     * Check if channel or a job is finished.
     * @param Channel
     * @param string
     * @return bool
     */
    public function isFinished(Channel $channel, $name = NULL) {
        $cid = $this->findChannel($channel);

        if ($name === NULL) {
            return empty($this->running[$cid]) && empty($this->queue[$cid]);
        } else {
            return isset($this->finished[$cid][$name]);
        }
    }


    /**
     * @param Channel
     * @param string
     * @return Response|bool
     */
    public function fetch(Channel $channel, $name = NULL) {
        $cid = $this->findChannel($channel);

        if ($name)
            return $this->fetchNamedJob($cid, $name);

        if (!empty($this->finished[$cid])) {
            foreach ($this->finished[$cid] as $name => $info) {
                return $this->fetchResultInt($cid, $name, $info);
            }
        }

        // start one job immediately
        if (empty($this->running[$cid])) {
            if (empty($this->queue[$cid]))
                return FALSE;

            foreach ($this->queue[$cid] as $name => $job) {
                $this->startJob($cid, $job, $name);
                break;
            }
            $this->waitForResult();
            $this->readResults();
        }

        // potentially endless loop, if something goes wrong (allways set request timeouts!)
        /// add timeout or retries
        while (empty($this->finished[$cid])) {
            $this->waitForResult();
            $this->readResults();
        }

        foreach ($this->finished[$cid] as $name => $x) {
            return $this->fetchResultInt($cid, $name);
        }

        return FALSE;
    }


    /**
     * @param string
     * @param string
     * @return Response
     */
    private function fetchNamedJob($cid, $name) {
        if (!isset($this->queue[$cid][$name])
            && !isset($this->running[$cid][$name])
            && !isset($this->finished[$cid][$name]))
            throw new RequestManagerException("Job named '$name' was not found.");

        // start job immediately
        if (isset($this->queue[$cid][$name])) {
            $this->startJob($cid, $this->queue[$cid][$name], $name);
            $this->waitForResult();
            $this->readResults();
        }

        // potentially endless loop, if something goes wrong (allways set request timeouts!)
        /// add timeout or retries
        while (!isset($this->finished[$cid][$name])) {
            $this->waitForResult();
            $this->readResults();
        }

        return $this->fetchResultInt($cid, $name);
    }


    /**
     * @param string
     * @param string
     * @return Response
     */
    private function fetchResultInt($cid, $name) {
        list($minfo, $request) = $this->finished[$cid][$name];
        
        $data = curl_multi_getcontent($minfo['handle']);
        
        $response = $request->createResponse($data, $minfo['result'], $name);

        $channel = & $this->channels[$cid];
        $channel['finished']--;
        $channel['fetched']++;
        unset($this->finished[$cid][$name]);

        if ($channel['threadLimit'] === -1)
            $this->removeChannelInt($cid);

        return $response;
    }


    // internals -------------------------------------------------------------------------------------------------------


    /**
     * Select channel to spawn new connection, taking in account channel priorities.
     * @return int|NULL
     */
    private function selectChannel() {
        if (count($this->resources) >= $this->threadLimit) return NULL;
        
        $selected = NULL;
        $ratio = -1000000;
        foreach ($this->channels as $cid => & $channel) {
            if ($selected === $cid) continue;
            if (!$this->selectable($cid)) continue;
            
            $channelRatio = ($channel['priority'] / $this->sumPriorities)
                - ($channel['running'] / $this->threadLimit);
            if ($channelRatio > $ratio) {
                $selected = $cid;
                $ratio = $channelRatio;
            }
        }
        
        return $selected;
    }

    
    private function selectable($cid) {
        if (empty($this->queue[$cid])) return FALSE;
        if (!empty($this->running[$cid]) 
            && count($this->running[$cid]) >= $this->channels[$cid]['threadLimit']) return FALSE;
        
        return TRUE;
    }
    

    /**
     * Start requests according to their priorities.
     */
    private function startJobs() {
        while ($cid = $this->selectChannel()) {
            foreach ($this->queue[$cid] as $name => $job) {
                $this->startJob($cid, $job, $name);
                break;
            }
        }
    }


    /**
     * Start a request in CURL.
     * @param int
     * @param string|array
     * @param string
     */
    private function startJob($cid, $job, $name) {
        $channel = & $this->channels[$cid];
        $channel['queued']--;
        $channel['running']++;
        unset($this->queue[$cid][$name]);
        if (empty($this->queue[$cid])) unset($this->queue[$cid]);

        if ($channel['threadLimit'] === -1) {
            $request = $channel['request'];
            
        } else {
            $request = clone $channel['request'];
            if (is_string($job)) {
                $request->appendUrl($job);
            } else {
                $request->setPostData($job);
            }
        }
        
        $request->prepare('', $name, FALSE);
        $handler = $request->getHandler();
        if ($err = curl_multi_add_handle($this->handler, $handler))
            throw new RequestManagerException("CURL error when adding a job: "
                . CurlHelpers::getCurlMultiErrorName($err), $err);

        if ($err = curl_multi_exec($this->handler, $active))
            throw new RequestManagerException("CURL error when starting jobs: "
                . CurlHelpers::getCurlMultiErrorName($err), $err);

        $this->running[$cid][$name] = 1;
        $this->resources[(string)$handler] = array($cid, $name, $request);
    }


    /**
     * Wait for any request to finish. Blocking.
     * @return bool
     */
    private function waitForResult() {
        if (!count($this->running)) return FALSE;

        do {
            if ($err = curl_multi_exec($this->handler, $running))
                throw new RequestManagerException("CURL error when executing jobs: "
                    . CurlHelpers::getCurlMultiErrorName($err), $err);

            $ready = curl_multi_select($this->handler);
            //if ($ready === -1)
            //    throw new RequestManagerException("CURL error when waiting for a result.");

            if ($ready > 0) return TRUE;

        } while ($running > 0 && $ready != -1);

        return FALSE;
    }


    /**
     * Read finished results from CURL
     */
    public function readResults() {
        while ($minfo = curl_multi_info_read($this->handler)) {
            // ok

            $resId = (string) $minfo['handle'];
            list($cid, $name, $request) = $this->resources[$resId];
            $channel = & $this->channels[$cid];

            $this->finished[$cid][$name] = array($minfo, $request);
            $channel['running']--;
            $channel['finished']++;
            unset($this->resources[$resId]);
            unset($this->running[$cid][$name]);
            if (empty($this->running[$cid])) unset($this->running[$cid]);
            
            if ($err = curl_multi_remove_handle($this->handler, $minfo['handle']))
                throw new RequestManagerException("CURL error when reading results: "
                    . CurlHelpers::getCurlMultiErrorName($err), $err);

            if ($channel['callback']) {
                $channel['callback']->invoke($this->fetchResultInt($cid, $name));
            }
        }
        $this->startJobs();
    }

}

