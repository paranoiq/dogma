<?php

namespace Dogma\Queue;


/**
 * Metadata storage for queue server
 */
interface IMetadataStorage
{


    /**
     * @param string
     * @param integer
     * @param string
     */
    public function insertJob($queue, $jobId, $data);


    /**
     * Get job id by job data
     * @param string
     * @param string
     * @return integer
     */
    public function findJob($queue, $data);


    /**
     * @param string
     * @param integer
     */
    public function deleteJob($queue, $jobId);


    /**
     * Clear metadata storage
     * @param string
     * @param \DateTime|null
     */
    public function clear($queue, \DateTime $time = null);

}
