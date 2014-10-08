<?php

namespace Dogma\Queue;


/**
 * Metadata storage for queue server
 */
interface IMetadataStorage {


    /**
     * @param string
     * @param int
     * @param string
     */
    function insertJob($queue, $jobId, $data);


    /**
     * Get job id by job data
     * @param string
     * @param string
     * @return int
     */
    function findJob($queue, $data);


    /**
     * @param string
     * @param int
     */
    function deleteJob($queue, $jobId);


    /**
     * Clear metadata storage
     * @param string
     * @param \DateTime|null
     */
    function clear($queue, \DateTime $time = NULL);

}
