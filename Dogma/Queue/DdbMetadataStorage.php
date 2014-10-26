<?php

namespace Dogma\Queue;


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


    public function __construct(\Dogma\Database\Connection $connection, $tablePrefix = 'queue_', $database = '')
    {
        $this->connection = $connection;
        $this->tablePrefix = $tablePrefix;
        $this->database = $database;
    }


    /**
     * @param string
     * @return string
     */
    private function getTable($queue)
    {
        return $this->database . '.' . $this->tablePrefix . $queue;
    }


    public function insertJob($queue, $jobId, $data)
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


    public function findJob($queue, $data)
    {
        return $this->connection->fetchColumn('SELECT `job_id` FROM ' . $this->getTable($queue) . 'WHERE `data` = ', $data);
    }


    public function deleteJob($queue, $jobId)
    {
        $this->connection->exec('DELETE ' . $this->getTable($queue) . 'WHERE `job_id` = ', $jobId);
    }


    public function clear($queue, \DateTime $time = null)
    {
        if ($time) {
            $this->connection->exec('DELETE ' . $this->getTable($queue) . 'WHERE `insert_time` <= ', $time);
        } else {
            $this->connection->exec('DELETE ' . $this->getTable($queue));
        }
    }

}
