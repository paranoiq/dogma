<?php

namespace Dogma\Queue;


/**
 * An interface to Beanstalk queue service. Implements Beanstalk protocol spec 1.2
 * @link https://github.com/kr/beanstalkd/blob/master/doc/protocol.txt
 *
 * Based on Socket_Beanstalk class by David Persson [nperson@gmx.de]
 * @link https://github.com/davidpersson/beanstalk/
 *
 * @author Vlasta Neubauer [paranoi@centrum.cz]
 */
class BeanstalkClient extends \Dogma\Object
{

    // job priority
    const TOP_PRIORITY = 0;
    const URGENT_PRIORITY = 512;
    const HIGH_PRIORITY = 1024; // everything under 1024 is urgent (see "current-jobs-urgent" in stats)
    const MEDIUM_PRIORITY = 2048;
    const LOW_PRIORITY = 4096;


    /** @var string */
    private $host;

    /** @var int */
    private $port;

    /** @var int */
    private $timeout;

    /** @var bool */
    private $persistent;

    /** @var resource */
    private $connection;


    protected $defaultPriority = 2048;
    protected $defaultTimeToRun = 60;
    protected $defaultDelay = 0;


    // suspended job handling
    const IGNORE = -2;
    const NOTICE = -1;
    const THROW_EXCEPTION = 0;

    /** @var int */
    private $onSuspended = 0;

    /** @var callback */
    private $onDeadline;

    public function __construct(string $host = '127.0.0.1', int $port = 11300, int $timeout = 1, bool $persistent = true)
    {
        $this->host = $host;
        $this->port = $port;
        $this->timeout = $timeout;
        $this->persistent = $persistent;
    }

    public function __destruct()
    {
        $this->quit();
    }

    public function setDefaultPriority(int $priority)
    {
        $this->defaultPriority = abs((int) $priority);
    }

    public function setDefaultDelay(int $delay)
    {
        $this->defaultDelay = abs((int) $delay);
    }

    public function setDefaultTimeToRun(int $timeToRun)
    {
        $this->defaultTimeToRun = abs((int) $timeToRun);
    }

    /**
     * Initiate a socket connection to Beanstalk server.
     */
    private function connect()
    {
        if (isset($this->connection)) {
            $this->disconnect();
        }

        $function = $this->persistent ? 'pfsockopen' : 'fsockopen';
        $errNum = $errStr = '';
        $params = [$this->host, $this->port, &$errNum, &$errStr];

        if ($this->timeout) {
            $params[] = $this->timeout;
        }
        $this->connection = @call_user_func_array($function, $params);

        if (!empty($errNum) || !empty($errStr)) {
            $this->connection = null;
            throw new BeanstalkException(sprintf('Socket: %s', $errStr), $errNum);
        }

        if (!is_resource($this->connection)) {
            $this->connection = null;
            throw new BeanstalkException('Cannot create connection to Beanstalk server.');
        }

        // no timeout (blocking reads)
        stream_set_timeout($this->connection, -1);
    }

    /**
     * Close connection to Beanstalk server.
     */
    private function disconnect()
    {
        if (is_resource($this->connection)) {
            fclose($this->connection);
        }

        $this->connection = null;
    }

    /**
     * Send a message to server.
     */
    private function send(string $data)
    {
        if (!$this->connection) {
            $this->connect();
        }
        $res = fwrite($this->connection, $data . "\r\n", strlen($data) + 2);

        if ($res === false) {
            throw new BeanstalkException('Cannot send message to Beanstalk server.');
        }
    }

    /**
     * Read a message from server.
     */
    private function receive(int $length = null): string
    {
        if ($length) {
            if (feof($this->connection)) {
                throw new BeanstalkException('No reply from Beanstalk server.');
            }
            $data = fread($this->connection, $length + 2);
            $meta = stream_get_meta_data($this->connection);

            if ($meta['timed_out']) {
                throw new BeanstalkException('Connection to Beanstalk server timed out.');
            }
            $data = rtrim($data, "\r\n");

        } else {
            $data = stream_get_line($this->connection, 16384, "\r\n");
            if ($data === false) {
                throw new BeanstalkException('No reply from Beanstalk server.');
            }
        }

        return $data;
    }

    /**
     * Send [QUIT] command and disconnect.
     */
    public function quit()
    {
        try {
            if ($this->connection) {
                $this->send('quit');
            }
        } catch (BeanstalkException $e) {
            // pass
        }
        $this->disconnect();
    }

    /**
     * Format delay argument to seconds.
     *
     * @param int|float|string|\DateTime
     * @return int
     */
    private function delayToSeconds($delay): int
    {
        if (is_numeric($delay)) {
            if ((int) $delay < 0) {
                trigger_error(sprintf('BeanstalkClient: Job delay should not be negative. %d given.', $delay), E_USER_WARNING);
            }

            return abs((int) $delay);

        } elseif (is_string($delay) || $delay instanceof \DateTime) {
            if (is_string($delay)) {
                $delay = new \DateTime($delay);
            }
            $seconds = $delay->getTimestamp() - time();
            if ($seconds < 0) {
                trigger_error(sprintf('BeanstalkClient: Job delay should not be negative. %d given.', $seconds), E_USER_WARNING);
            }

            return abs($seconds);

        } else {
            throw new \InvalidArgumentException('Unsupported delay parameter given.');
        }
    }

    /**
     * Try to serialize job data.
     * Throws exception for unsupported types (null, bool, resource)
     *
     * @param array|object|int|float
     * @return string
     */
    private function serializeJob($data): string
    {
        if (is_object($data) || is_array($data) || is_int($data) || is_float($data)) {
            return serialize($data);
        } else {
            throw new \InvalidArgumentException('Unsupported job data type.');
        }
    }

    /**
     * Try to unserialize job data.
     *
     * @param string
     * @return string|array|object
     */
    private function unserializeJob(string $data)
    {
        $job = @unserialize($data);

        if ($job === false) {
            return $data;
        } else {
            return $job;
        }
    }

    /**
     * Set callback to call when a "DEADLINE SOON" signal received.
     */
    public function setOnDeadline(callable $callback)
    {
        $this->onDeadline = $callback;
    }

    /**
     * What to do if job is suspended by server.
     */
    public function setOnSuspended(int $action)
    {
        $this->onSuspended = $action;
    }

    /**
     * Handle case when a job is suspended *by server*.
     */
    private function suspended(int $jobId)
    {
        switch ($this->onSuspended) {
            case self::IGNORE:
                break;
            case self::NOTICE:
                trigger_error(sprintf('BeanstalkClient: Job %s was suspended by server. Check and restore the suspended jobs!', $jobId));
                break;
            case self::THROW_EXCEPTION:
            default:
                throw new BeanstalkException(sprintf('BeanstalkClient: Job %s was suspended by server. Check and restore the suspended jobs!', $jobId));
        }
    }

    // Producer Commands -----------------------------------------------------------------------------------------------

    /**
     * Insert a job into the queue. [PUT]
     * All other types except string will be serialized.
     *
     * @param string $data job data
     * @param int|\DateTime $delay seconds of delay or time to start
     * @param int $priority [0-2^32]. lower number means higher priority
     * @param int $timeToRun worker timeout, before re-assigning job to another worker
     * @return int job id
     */
    public function queue(string $data, $delay = null, int $priority = null, int $timeToRun = null): int
    {
        if (!isset($priority)) {
            $priority = $this->defaultPriority;
        }
        if (!isset($timeToRun)) {
            $timeToRun = $this->defaultTimeToRun;
        }
        if (!isset($delay)) {
            $delay = $this->defaultDelay;
        }

        $priority  = abs((int) $priority);
        $timeToRun = abs((int) $timeToRun);
        if (!is_int($delay)) {
            $delay = $this->delayToSeconds($delay);
        }

        if (!is_string($data)) {
            $data = $this->serializeJob($data);
        }

        $this->send(sprintf('put %d %d %d %d', $priority, $delay, $timeToRun, strlen($data)));
        $this->send($data);

        $status = strtok($this->receive(), ' ');

        switch ($status) {
            case 'INSERTED':
                return (int) strtok(' '); // job id
            case 'BURIED':
                $this->suspended((int) strtok(' '));
                return -1;
            case 'EXPECTED_CRLF':
            case 'JOB_TOO_BIG':
            default:
                throw new BeanstalkException(sprintf('Error when queueing a job: %s', $status));
        }
    }

    /**
     * Select queue for inserting jobs. Default queue is "default". [USE]
     * Automatically creates queues.
     */
    public function selectQueue(string $queue)
    {
        $this->send(sprintf('use %s', $queue));
        $status = strtok($this->receive(), ' ');

        switch ($status) {
            case 'USING':
                break;
            default:
                throw new BeanstalkException(sprintf('Error when selecting a queue: %s', $status));
        }
    }

    // Worker Commands -------------------------------------------------------------------------------------------------

    /**
     * Ask for a job to assign. Job is reserved until finished, released or timed-out. [RESERVE]
     * When no timeout is given, waits until some job is ready.
     */
    public function assign(int $timeout = null): BeanstalkJob
    {
        if (isset($timeout)) {
            $this->send(sprintf('reserve-with-timeout %d', $timeout));
        } else {
            $this->send('reserve');
        }
        $status = strtok($this->receive(), ' ');

        switch ($status) {
            case 'RESERVED':
                $id = (int) strtok(' ');
                $body = $this->unserializeJob($this->receive((int) strtok(' ')));
                break;
            case 'DEADLINE_SOON':
                /// if ($this->onDeadline) $this->onDeadline();
                return [];
            case 'TIMED_OUT':
                return [];
            default:
                throw new BeanstalkException(sprintf('Error when claiming a job: %s', $status));
        }

        return new BeanstalkJob($id, $body, true, $this);
    }

    /**
     * Finishes job and removes it from the queue. [DELETE]
     */
    public function finish(int $jobId)
    {
        $this->send(sprintf('delete %d', $jobId));
        $status = $this->receive();

        switch ($status) {
            case 'DELETED':
                return;
            case 'NOT_FOUND':
            default:
                throw new BeanstalkException(sprintf('Error when finishing a job: %s', $status));
        }
    }

    /**
     * Alias for finish().
     */
    public function delete(int $jobId)
    {
        $this->finish($jobId);
    }

    /**
     * Puts a reserved job back into the ready queue. [RELEASE]
     *
     * @param int
     * @param int|\DateTime
     * @param int
     */
    public function release(int $jobId, $delay = null, int $priority = null)
    {
        if (!isset($priority)) {
            $priority = $this->defaultPriority;
        }
        if (!isset($delay)) {
            $delay = $this->defaultDelay;
        }

        $priority = abs((int) $priority);
        if (!is_int($delay)) {
            $delay = $this->delayToSeconds($delay);
        }

        $this->send(sprintf('release %d %d %d', $jobId, $priority, $delay));
        $status = $this->receive();

        switch ($status) {
            case 'RELEASED':
                return;
            case 'BURIED':
                $this->suspended($jobId);
                return;
            case 'NOT_FOUND':
            default:
                throw new BeanstalkException(sprintf('Error when releasing a job: %s', $status));
        }
    }

    /**
     * Suspend a job. Job cannot be assigned to a worker until it is restored. [BURY]
     */
    public function suspend(int $jobId, int $priority = null)
    {
        if (!isset($priority)) {
            $priority = $this->defaultPriority;
        }

        $priority = abs((int) $priority);

        $this->send(sprintf('bury %d %d', $jobId, $priority));
        $status = $this->receive();

        switch ($status) {
            case 'BURIED':
                return;
            case 'NOT_FOUND':
            default:
                throw new BeanstalkException(sprintf('Error when suspending a job: %s', $status));
        }
    }

    /**
     * Restore a previously suspended job. It can be assigned to a worker now. [KICK*]
     */
    public function restore(int $jobs): int
    {
        /// check for suspended (do not kick delayed jobs!)

        $this->send(sprintf('kick %d', $jobs));
        $status = strtok($this->receive(), ' ');

        switch ($status) {
            case 'KICKED':
                return (int) strtok(' ');
            default:
                throw new BeanstalkException(sprintf('Error when restoring jobs: %s', $status));
        }
    }

    /**
     * Reset the "time to run" of the job. [TOUCH]
     */
    public function touch(int $jobId)
    {
        $this->send(sprintf('touch %d', $jobId));
        $status = $this->receive();

        switch ($status) {
            case 'TOUCHED':
                return;
            case 'NOT_TOUCHED':
            default:
                throw new BeanstalkException(sprintf('Error when touching a job: %s', $status));
        }
    }

    /**
     * Watch queue. Jobs are claimed only from wathed queues. [WATCH]
     */
    public function watchQueue(string $queue)
    {
        $this->send(sprintf('watch %s', $queue));
        $status = strtok($this->receive(), ' ');

        switch ($status) {
            case 'WATCHING':
                return;
            default:
                throw new BeanstalkException(sprintf('Error when watching a queue: %s', $status));
        }
    }

    /**
     * Ignore queue. Jobs are claimed only from wathed queues. [WATCH]
     */
    public function ignoreQueue(string $queue)
    {
        $this->send(sprintf('ignore %s', $queue));
        $status = strtok($this->receive(), ' ');

        switch ($status) {
            case 'WATCHING':
                return;
            case 'NOT_IGNORED':
            default:
                throw new BeanstalkException(sprintf('Error when ignoring a queue: %s', $status));
        }
    }

    /**
     * Pause queue. No jobs from this queue will be assigned until the given time. [PAUSE-TUBE]
     *
     * @param string
     * @param int|\DateTime seconds of delay or time to start
     */
    public function pauseQueue(string $queue, $delay)
    {
        if (!is_int($delay)) {
            $delay = $this->delayToSeconds($delay);
        }

        $this->send(sprintf('pause-tube %s %d', $queue, $delay));
        $status = strtok($this->receive(), ' ');

        switch ($status) {
            case 'WATCHING':
                return;
            case 'NOT_IGNORED':
            default:
                throw new BeanstalkException(sprintf('Error when ignoring a queue: %s', $status));
        }
    }

    // Show Commands --------------------------------------------------------------------------------------------------

    /**
     * Show a job. [PEEK]
     *
     * @param int
     * @param bool $stats with statistics
     * @return \Dogma\Queue\BeanstalkJob|null
     */
    public function showJob(int $jobId, bool $stats = false)
    {
        $this->send(sprintf('peek %d', $jobId));
        return $this->readJob($stats);
    }

    /**
     * Show the next ready job. [PEEK-READY]
     *
     * @param bool $stats with statistics
     * @return \Dogma\Queue\BeanstalkJob|null
     */
    public function showNextReadyJob(bool $stats = false)
    {
        $this->send('peek-ready');
        return $this->readJob($stats);
    }

    /**
     * Show the job with the shortest delay left. [PEEK-DELAYED]
     *
     * @param bool $stats with statistics
     * @return \Dogma\Queue\BeanstalkJob|null
     */
    public function showNextDelayedJob(bool $stats = false)
    {
        $this->send('peek-delayed');
        return $this->readJob($stats);
    }

    /**
     * Inspect the next job in the list of buried jobs. [PEEK-BURIED]
     *
     * @param bool $stats with statistics
     * @return \Dogma\Queue\BeanstalkJob|null
     */
    public function showNextSuspendedJob(bool $stats = false)
    {
        $this->send('peek-buried');
        return $this->readJob($stats);
    }

    /**
     * Handles response for all show methods.
     *
     * @param bool $stats with statistics
     * @return \Dogma\Queue\BeanstalkJob|null
     */
    private function readJob(bool $stats)
    {
        $status = strtok($this->receive(), ' ');

        switch ($status) {
            case 'FOUND':
                $id = (int) strtok(' ');
                $data = $this->unserializeJob($this->receive((int) strtok(' ')));
                break;
            case 'NOT_FOUND':
                return null;
            default:
                throw new BeanstalkException(sprintf('Error when reading a job: %s', $status));
        }

        if ($stats) {
            $st = $this->getJobStats($id);
        } else {
            $st = [];
        }

        return new BeanstalkJob($id, $data, false, $this, $st);
    }

    // Stats Commands --------------------------------------------------------------------------------------------------

    /**
     * Get statistical information about a job. [STATS-JOB]
     *
     * @param int
     * @return mixed[]
     */
    public function getJobStats(int $jobId): array
    {
        $this->send(sprintf('stats-job %d', $jobId));
        return $this->readStats();
    }

    /**
     * Get statistical information about a queue. [STATS-TUBE]
     *
     * @param string $queue name
     * @return mixed[]
     */
    public function getQueueStats(string $queue): array
    {
        $this->send(sprintf('stats-tube %s', $queue));
        return $this->readStats();
    }

    /**
     * Get statistical information about the server. [STATS]
     *
     * @return mixed[]
     */
    public function getServerStats(): array
    {
        $this->send('stats');
        return $this->readStats();
    }

    /**
     * Get a list of all server queues. [LIST-TUBES]
     *
     * @return string[]
     */
    public function getQueues(): array
    {
        $this->send('list-tubes');
        return $this->readStats();
    }

    /**
     * Get selected queue. [LIST-TUBE-USED]
     */
    public function getSelectedQueue(): string
    {
        $this->send('list-tube-used');
        return $this->readStats();
    }

    /**
     * Get list of fatched queues. [LIST-TUBES-WATCHED]
     *
     * @return string[]
     */
    public function getWatchedQueues(): array
    {
        $this->send('list-tubes-watched');
        return $this->readStats();
    }

    /**
     * Handles responses for all stat methods.
     *
     * @return mixed[]
     */
    private function readStats(): array
    {
        $status = strtok($this->receive(), ' ');

        switch ($status) {
            case 'OK':
                $response = $this->receive((int) strtok(' '));
                return $this->decodeYaml($response);
            default:
                throw new BeanstalkException(sprintf('Error when reading stats: %s', $status));
        }
    }

    /**
     * Decodes YAML data. This is a super naive decoder which just works on a
     * subset of YAML which is commonly returned by beanstalk.
     */
    private function decodeYaml(string $data): array
    {
        $data = array_slice(explode("\n", $data), 1);
        $result = [];

        foreach ($data as $key => $value) {
            if ($value[0] === '-') {
                $value = ltrim($value, '- ');

            } elseif (strpos($value, ':') !== false) {
                list($key, $value) = explode(':', $value);
                $value = ltrim($value, ' ');
            }
            if (is_numeric($value)) {
                $value = (int) $value == $value ? (int) $value : (float) $value;
            }
            $result[$key] = $value;
        }
        return $result;
    }

}
