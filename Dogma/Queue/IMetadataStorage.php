<?php

namespace Dogma\Queue;


/**
 * Metadata storage for queue server
 */
interface IMetadataStorage
{

    public function insertJob(string $queue, int $jobId, string $data);

    /**
     * Get job id by job data
     */
    public function findJob(string $queue, string $data): int;

    public function deleteJob(string $queue, int $jobId);

    /**
     * Clear metadata storage
     *
     * @param string
     * @param \DateTime|null
     */
    public function clear(string $queue, \DateTime $time = null);

}
