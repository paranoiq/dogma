<?php

namespace Dogma\Queue;


/**
 * An interface to Beanstalk queue service
 */
class BeanstalkClientExtended extends BeanstalkClient
{

    /** @var \Dogma\Queue\IMetadataStorage */
    private $storage;

    public function __construct(
        IMetadataStorage $storage,
        string $host = '127.0.0.1',
        int $port = 11300,
        int $timeout = 1,
        bool $persistent = true
    ) {
        parent::__construct($host, $port, $timeout, $persistent);

        $this->storage = $storage;
    }

    /**
     * Insert a job into the queue. Do not insert if it is already there.
     * All other types except string will be serialized.
     *
     * @param string $data
     * @param int $delay seconds of delay or time to start
     * @param int $priority [0-2^32]. lower number means higher priority
     * @param int $timeToRun worker timeout, before re-assigning job to another worker
     * @return int job id
     */
    public function queueUnique(string $data, int $delay = null, int $priority = null, int $timeToRun = null): int
    {
        if (!$this->storage) {
            throw new BeanstalkException('Storage must be set to insert unique tasks!');
        }

        if ($jobId = $this->storage->findJob($this->getSelectedQueue(), $data)) {
            $job = $this->showJob($jobId, true);
            if (!$job) {
                $this->storage->deleteJob($this->getSelectedQueue(), $jobId);
            } elseif (!$job->isReserved()) {
                $job->delete();
            }
        }

        $jobId = $this->queue($data, $delay, $priority, $timeToRun);
        $this->storage->insertJob($this->getSelectedQueue(), $jobId, $data);
    }

}
