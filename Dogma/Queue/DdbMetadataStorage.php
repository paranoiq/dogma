<?php

namespace Dogma\Queue;

use Dogma\Database\Connection;


/**
 * Stores metadata (data -> id) for Beanstalk queue
 *
 * Expects tables of name {database}.{prefix}{queue_name} to exist with columns:
 * - job_id int
 * - data varchar
 * - insert_time datetime
 */
class DdbMetadataStorage extends \Dogma\Object implements IMetadataStorage
{

    /** @var \Dogma\Database\Connection */
    private $connection;

    /** @var string */
    private $tablePrefix;

    /** @var string */
    private $database;

    public function __construct(Connection $connection, $tablePrefix = 'queue_', $database = '')
    {
        $this->connection = $connection;
        $this->tablePrefix = $tablePrefix;
        $this->database = $database;
    }

    private function getTable(string $queue): string
    {
        return $this->database . '.' . $this->tablePrefix . $queue;
    }

    public function insertJob(string $queue, int $jobId, string $data)
    {
        $this->connection->exec(
            'INSERT INTO ' . $this->getTable($queue) . 'VALUES ',
            [
                'job_id' => $jobId,
                'data' => $data,
                'insert_time' => new \DateTime
            ]
        );
    }

    public function findJob(string $queue, string $data)
    {
        return $this->connection->fetchColumn('SELECT `job_id` FROM ' . $this->getTable($queue) . 'WHERE `data` = ', $data);
    }

    public function deleteJob(string $queue, int $jobId)
    {
        $this->connection->exec('DELETE ' . $this->getTable($queue) . 'WHERE `job_id` = ', $jobId);
    }

    public function clear(string $queue, \DateTime $time = null)
    {
        if ($time) {
            $this->connection->exec('DELETE ' . $this->getTable($queue) . 'WHERE `insert_time` <= ', $time);
        } else {
            $this->connection->exec('DELETE ' . $this->getTable($queue));
        }
    }

}
