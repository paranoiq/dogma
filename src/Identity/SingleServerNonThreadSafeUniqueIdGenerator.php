<?php
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Identity;

use Dogma\System\Php;
use Dogma\Time\TimeProvider;

/**
 * Unique 63bit (unsigned) integer id generator.
 *
 * Based on:
 * - system time (count of seconds since 2016-01-01, valid till year 2084; 31 bits)
 * - process id (max 1M process ids; 20 bits)
 * - counter (max 4K generated ids per second and process; 12 bits)
 *
 * Bits:
 * XSSSSSSS SSSSSSSS SSSSSSSS SSSSSSSS PPPPPPPP PPPPPPPP PPPPCCCC CCCCCCCC
 * - X is unused (sign)
 * - S is seconds
 * - P is process id
 * - C is counter
 *
 * Use for single-server applications only! Do not use if PHP is running as Apache module!
 * Will break if:
 * - server time is changed backwards
 * - running in a multi-threaded environment (Apache mod_php or pthreads extension; more threads with same process id)
 * - your OS is configured to reuse recently released process ids (two processes with the same id within one second)
 *
 * Use Uuid or prefixed Uid for more servers.
 */
class SingleServerNonThreadSafeUniqueIdGenerator implements \Dogma\Identity\UidGenerator
{
    use \Dogma\StrictBehaviorMixin;
    use \Dogma\NonCloneableMixin;
    use \Dogma\NonSerializableMixin;

    // timestamp of 2016-01-01 00:00:00 UTC
    public const BASE_TIMESTAMP = 1451606400;

    /** @var \Dogma\Time\TimeProvider */
    private $timeProvider;

    /** @var \DateTimeZone */
    private $utcTimeZone;

    /** @var int */
    private $lastTimestamp;

    /** @var int */
    private $pid;

    /** @var int */
    private $counter;

    public function __construct(TimeProvider $timeProvider)
    {
        if (Php::is32bit()) {
            throw new \Dogma\Identity\IdGeneratorException('Cannot safely generate unique ids. Running on a 32bit OS.');
        }
        if (Php::isMultithreaded()) {
            throw new \Dogma\Identity\IdGeneratorException('Cannot safely generate unique ids. Running in multithreaded environment.');
        }

        $this->timeProvider = $timeProvider;
        $this->utcTimeZone = new \DateTimeZone('UTC');

        // default limit for pid on 32bit Linux is 15 bits (32K). can be configured up to 22 bits (4M) on 64bit Linux
        // taking only 20 bits (1M) to leave some more space for counter
        /// todo: check /proc/sys/kernel/pid_max?
        $pid = getmypid();
        if ($pid > 0xFFFFF) {
            throw new \Dogma\Identity\IdGeneratorException('Cannot safely generate unique ids. Process id too high.');
        }
        $this->pid = $pid << 12;
    }

    public function createId(int $retry = 0): int
    {
        $timestamp = $this->timeProvider->getDateTime()->setTimezone($this->utcTimeZone)->getTimestamp();
        if ($timestamp < $this->lastTimestamp) {
            throw new \Dogma\Identity\IdGeneratorException('Cannot safely generate unique id. System time has changed.');
        } elseif ($timestamp === $this->lastTimestamp) {
            $this->counter++;
        } else {
            $this->counter = 0;
            $this->lastTimestamp = $timestamp;
        }
        $seconds = (($timestamp - self::BASE_TIMESTAMP) & 0x7FFFFFFF) << 32;

        if ($this->counter >= 0xFFF) {
            if ($retry > 4) {
                throw new \Dogma\Identity\IdGeneratorException('Cannot safely generate unique id. Counter overflow.');
            } else {
                usleep(250000);
                return $this->createId($retry + 1);
            }
        }

        return $seconds + $this->pid + $this->counter;
    }

}
