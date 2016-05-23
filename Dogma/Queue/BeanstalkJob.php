<?php

namespace Dogma\Queue;


/**
 * Beanstalk job representation returned by BeanstalkClient
 *
 * @property-read $id
 * @property-read $data
 * @property-read $queue
 * @property-read $status
 * @property-read $priority
 * @property-read $age (seconds) time from creating
 * @property-read $timeLeft (seconds) time before reassigning
 * @property-read $firstBinlogFileName
 * @property-read $reserved
 * @property-read $timeouts
 * @property-read $released
 * @property-read $suspended
 * @property-read $restored
 */
class BeanstalkJob extends \Dogma\Object
{

    const DELAYED = 'delayed';
    const READY = 'ready';
    const RESERVED = 'reserved';
    const SUSPENDED = 'suspended';

    /** @var BeanstalkClient */
    private $connection;

    /** @var int */
    private $id;

    /** @var mixed */
    private $data;

    /** @var bool */
    private $owned;

    /** @var array */
    private $stats;

    /**
     * @param int
     * @param mixed
     * @param bool
     * @param \Dogma\Queue\BeanstalkClient
     * @param array
     */
    public function __construct(int $id, $data, bool $owned, BeanstalkClient $connection, array $stats = [])
    {
        $this->id = $id;
        $this->data = $data;
        $this->owned = $owned;
        $this->connection = $connection;
        $this->stats = $stats;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function touch()
    {
        $this->connection->touch($this->id);
    }

    /**
     * @param int|\DateTime
     * @param int
     */
    public function release($delay = null, int $priority = null)
    {
        $this->connection->release($this->id, $delay, $priority);
    }

    public function finish()
    {
        $this->connection->finish($this->id);
    }

    public function delete()
    {
        $this->connection->delete($this->id);
    }

    public function suspend(int $priority = null)
    {
        $this->connection->suspend($this->id, $priority);
    }

    public function restore()
    {
        $this->connection->restore($this->id);
    }

    // job info & statistics -------------------------------------------------------------------------------------------

    public function isOwned(): bool
    {
        return $this->owned;
    }

    public function isDelayed(): bool
    {
        return $this->__get('status') === self::DELAYED;
    }

    public function isReady(): bool
    {
        return $this->__get('status') === self::READY;
    }

    public function isReserved(): bool
    {
        if ($this->isOwned()) {
            return true;
        }
        return $this->__get('status') === self::RESERVED;
    }

    public function isSuspended(): bool
    {
        return $this->__get('status') === self::SUSPENDED;
    }

    private function loadStats()
    {
        $this->stats = $this->connection->getJobStats($this->id);
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function &__get(string $name)
    {
        if (!$this->stats) {
            $this->loadStats();
        }

        static $fields = [
            'queue' => 'tube',
            'status' => 'state',
            'priority' => 'pri',
            'age' => 'age',
            'timeLeft' => 'time-left',
            'firstBinlogFileName' => 'file',
            'reserved' => 'reserves',
            'timeouts' => 'timeouts',
            'released' => 'releases',
            'suspended' => 'buries',
            'restored' => 'kicks'
        ];

        static $states = [
            'delayed' => self::DELAYED,
            'ready' => self::READY,
            'reserved' => self::RESERVED,
            'buried' => self::SUSPENDED
        ];

        if (!isset($this->stats[$fields[$name]])) {
            return parent::__get($name);
        }

        $val = $this->stats[$fields[$name]];
        if ($name === 'status') {
            $val = $states[$val];
        }

        return $val;
    }

}

/*
 - "id" is the job id

 - "tube" is the name of the tube that contains this job

 - "state" is "ready" or "delayed" or "reserved" or "buried"

 - "pri" is the priority value set by the put, release, or bury commands.

 - "age" is the time in seconds since the put command that created this job.

 - "time-left" is the number of seconds left until the server puts this job
   into the ready queue. This number is only meaningful if the job is
   reserved or delayed. If the job is reserved and this amount of time
   elapses before its state changes, it is considered to have timed out.

 - "file" is the number of the earliest binlog file containing this job.
   If -b wasn't used, this will be 0.

 - "reserves" is the number of times this job has been reserved.

 - "timeouts" is the number of times this job has timed out during a
   reservation.

 - "releases" is the number of times a client has released this job from a
   reservation.

 - "buries" is the number of times this job has been buried.

 - "kicks" is the number of times this job has been kicked.
*/
