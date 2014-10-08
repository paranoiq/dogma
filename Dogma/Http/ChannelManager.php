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
 * Manages parallel requests over multiple HTTP channels.
 */
class ChannelManager extends \Dogma\Object {


    /** @var resource */
    private $handler;

    /** @var int maximum threads for all channles */
    private $threadLimit = 20;

    /** @var float sum of priorities of all channels */
    private $sumPriorities = 0.0;

    /** @var Channel[] */
    private $channels = array();

    /** @var array ($resourceId => array($channelId, $jobName, $request)) */
    private $resources = array();



    public function __construct() {
        if (!$this->handler = curl_multi_init())
            throw new ChannelException("Cannot initialize CURL multi-request.");
    }


    public function __destruct() {
        if ($this->handler) curl_multi_close($this->handler);
    }


    /**
     * @return resource
     */
    public function getHandler() {
        return $this->handler;
    }


    /**
     * Set maximum of request to run paralelly.
     * @param int
     * @return self
     */
    public function setThreadLimit($threads) {
        $this->threadLimit = abs($threads);

        return $this;
    }


    /**
     * @param Channel
     * @return self
     */
    public function addChannel(Channel $channel) {
        $this->channels[spl_object_hash($channel)] = $channel;
        $this->updatePriorities();
        $this->startJobs();

        return $this;
    }


    public function updatePriorities() {
        $sum = 0;
        foreach ($this->channels as $channel) {
            $sum += $channel->getPriority();
        }
        $this->sumPriorities = $sum;
    }


    /**
     * @return self
     */
    public function read() {
        $this->waitForResult();
        $this->readResults();

        return $this;
    }


    /**
     * Wait for any request to finish. Blocking.
     * @return int
     */
    private function waitForResult() {
        $run = FALSE;
        foreach ($this->channels as $channel) {
            if (!$channel->isFinished()) $run = TRUE;
        }
        if (!$run) return 0;

        do {
            $active = $this->exec();
            $ready = curl_multi_select($this->handler);

            if ($ready > 0) return $ready;

        } while ($active > 0 && $ready != -1);

        return 0;
    }


    /**
     * @return int
     */
    public function exec() {
        do {
            $err = curl_multi_exec($this->handler, $active);
            if ($err > 0)
                throw new ChannelException("CURL error when starting jobs: "
                    . CurlHelpers::getCurlMultiErrorName($err), $err);
        } while ($err === CURLM_CALL_MULTI_PERFORM);

        return $active;
    }


    /**
     * Read finished results from CURL.
     */
    private function readResults() {
        while ($minfo = curl_multi_info_read($this->handler)) {
            $resourceId = (string) $minfo['handle'];
            list($cid, $name, $request) = $this->resources[$resourceId];
            $channel = & $this->channels[$cid];

            if ($err = curl_multi_remove_handle($this->handler, $minfo['handle']))
                throw new ChannelException("CURL error when reading results: "
                    . CurlHelpers::getCurlMultiErrorName($err), $err);

            $channel->jobFinished($name, $minfo, $request);
            unset($this->resources[$resourceId]);
        }
        $this->startJobs();
    }


    /**
     * Start requests according to their priorities.
     */
    public function startJobs() {
        while ($cid = $this->selectChannel()) {
            $this->channels[$cid]->startJob();
        }
        $this->exec();
    }


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
            if (!$channel->canStartJob()) continue;

            $channelRatio = ($channel->getPriority() / $this->sumPriorities)
                - ($channel->getRunningJobCount() / $this->threadLimit);
            if ($channelRatio > $ratio) {
                $selected = $cid;
                $ratio = $channelRatio;
            }
        }

        return $selected;
    }


    /**
     * Save data for later use.
     * @param resource
     * @param Channel
     * @param string|int
     * @param Request
     */
    public function jobStarted($resource, $channel, $name, $request) {
        $this->resources[(string) $resource] = array(spl_object_hash($channel), $name, $request);
    }

}
