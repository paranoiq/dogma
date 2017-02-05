<?php
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Http\Channel;

use Dogma\Http\Curl\CurlHelper;
use Dogma\Http\HeaderParser;
use Dogma\Http\Request;
use Dogma\Time\CurrentTimeProvider;

/**
 * Manages parallel requests over multiple HTTP channels.
 */
class ChannelManager
{
    use \Dogma\StrictBehaviorMixin;
    use \Dogma\NonSerializableMixin;
    use \Dogma\NonCloneableMixin;

    /** @var resource(curl) */
    private $handler;

    /** @var int maximum threads for all channels */
    private $threadLimit = 20;

    /** @var float sum of priorities of all channels */
    private $sumPriorities = 0.0;

    /** @var \Dogma\Http\Channel\Channel[] */
    private $channels = [];

    /** @var array ($resourceId => array($channelId, $jobName, $request)) */
    private $resources = [];

    /** @var \Dogma\Http\HeaderParser */
    private $headerParser;

    public function __construct(HeaderParser $headerParser = null)
    {
        $this->headerParser = $headerParser;
        if (!$this->handler = curl_multi_init()) {
            throw new \Dogma\Http\Channel\ChannelException('Cannot initialize CURL multi-request.');
        }
    }

    public function __destruct()
    {
        if ($this->handler) {
            curl_multi_close($this->handler);
        }
    }

    /**
     * @return resource
     */
    public function getHandler()
    {
        return $this->handler;
    }

    /**
     * Set maximum of request to run in parallel.
     */
    public function setThreadLimit(int $threads)
    {
        $this->threadLimit = abs($threads);
    }

    public function addChannel(Channel $channel)
    {
        $this->channels[spl_object_hash($channel)] = $channel;
        $this->updatePriorities();
        $this->startJobs();
    }

    private function updatePriorities()
    {
        $sum = 0;
        foreach ($this->channels as $channel) {
            $sum += $channel->getPriority();
        }
        $this->sumPriorities = $sum;
    }

    public function read()
    {
        $this->waitForResult();
        $this->readResults();
    }

    /**
     * Wait for any request to finish. Blocking. Returns count of available results.
     */
    private function waitForResult(): int
    {
        $run = false;
        foreach ($this->channels as $channel) {
            if (!$channel->isFinished()) {
                $run = true;
            }
        }
        if (!$run) {
            return 0;
        }

        do {
            $active = $this->exec();
            $ready = curl_multi_select($this->handler);

            if ($ready > 0) {
                return $ready;
            }

        } while ($active > 0 && $ready != -1);

        return 0;
    }

    public function exec(): int
    {
        do {
            $error = curl_multi_exec($this->handler, $active);
            if ($error > 0) {
                throw new ChannelException('CURL error when starting jobs: ' . CurlHelper::getCurlMultiErrorName($error), $error);
            }
        } while ($error === CURLM_CALL_MULTI_PERFORM);

        return $active;
    }

    /**
     * Read finished results from CURL.
     */
    private function readResults()
    {
        while ($info = curl_multi_info_read($this->handler)) {
            $resourceId = (string) $info['handle'];
            list($cid, $name, $request) = $this->resources[$resourceId];
            $channel = & $this->channels[$cid];

            $error = curl_multi_remove_handle($this->handler, $info['handle']);
            if ($error) {
                throw new ChannelException('CURL error when reading results: ' . CurlHelper::getCurlMultiErrorName($error), $error);
            }

            $channel->jobFinished($name, $info, $request);
            unset($this->resources[$resourceId]);
        }
        $this->startJobs();
    }

    /**
     * Start requests according to their priorities.
     */
    public function startJobs()
    {
        while ($cid = $this->selectChannel()) {
            $this->channels[$cid]->startJob();
        }
        $this->exec();
    }

    /**
     * Select channel to spawn new connection, taking in account channel priorities.
     * @return int|null
     */
    private function selectChannel()
    {
        if (count($this->resources) >= $this->threadLimit) {
            return null;
        }

        $selected = null;
        $ratio = PHP_INT_MIN;
        foreach ($this->channels as $cid => &$channel) {
            if ($selected === $cid) {
                continue;
            }
            if (!$channel->canStartJob()) {
                continue;
            }

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
     * @param \Dogma\Http\Channel
     * @param string|int
     * @param \Dogma\Http\Request
     */
    public function jobStarted($resource, Channel $channel, $name, Request $request)
    {
        $this->resources[(string) $resource] = [spl_object_hash($channel), $name, $request];
    }

    public function getHeaderParser(): HeaderParser
    {
        if ($this->headerParser === null) {
            $this->headerParser = new HeaderParser(new CurrentTimeProvider());
        }
        return $this->headerParser;
    }

}