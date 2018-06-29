<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

// spell-check-ignore: BYMONTH BYWEEKNO BYYEARDAY BYMONTHDAY BYDAY BYHOUR BYMINUTE BYSECOND BYSETPOS

namespace Dogma\Time\Repetition\Ical;

use Dogma\StrictBehaviorMixin;

/**
 * Specification: https://tools.ietf.org/html/rfc5545
 *
 * BY.. rule behavior:
 * +----------+--------+--------+-------+-------+------+-------+------+
 * |          |SECONDLY|MINUTELY|HOURLY |DAILY  |WEEKLY|MONTHLY|YEARLY|
 * +----------+--------+--------+-------+-------+------+-------+------+
 * |BYMONTH   |Limit   |Limit   |Limit  |Limit  |Limit |Limit  |Expand|
 * +----------+--------+--------+-------+-------+------+-------+------+
 * |BYWEEKNO  |N/A     |N/A     |N/A    |N/A    |N/A   |N/A    |Expand|
 * +----------+--------+--------+-------+-------+------+-------+------+
 * |BYYEARDAY |Limit   |Limit   |Limit  |N/A    |N/A   |N/A    |Expand|
 * +----------+--------+--------+-------+-------+------+-------+------+
 * |BYMONTHDAY|Limit   |Limit   |Limit  |Limit  |N/A   |Expand |Expand|
 * +----------+--------+--------+-------+-------+------+-------+------+
 * |BYDAY     |Limit   |Limit   |Limit  |Limit  |Expand|Note 1 |Note 2|
 * +----------+--------+--------+-------+-------+------+-------+------+
 * |BYHOUR    |Limit   |Limit   |Limit  |Expand |Expand|Expand |Expand|
 * +----------+--------+--------+-------+-------+------+-------+------+
 * |BYMINUTE  |Limit   |Limit   |Expand |Expand |Expand|Expand |Expand|
 * +----------+--------+--------+-------+-------+------+-------+------+
 * |BYSECOND  |Limit   |Expand  |Expand |Expand |Expand|Expand |Expand|
 * +----------+--------+--------+-------+-------+------+-------+------+
 * |BYSETPOS  |Limit   |Limit   |Limit  |Limit  |Limit |Limit  |Limit |
 * +----------+--------+--------+-------+-------+------+-------+------+
 *
 * Note 1: Limit if BYMONTHDAY is present; otherwise, special expand for MONTHLY.
 * Note 2: Limit if BYYEARDAY or BYMONTHDAY is present; otherwise, special expand for WEEKLY if BYWEEKNO present;
 *         otherwise, special expand for MONTHLY if BYMONTH present; otherwise, special expand for YEARLY.
 */
class Rrule
{
    use StrictBehaviorMixin;

    /** @var \Dogma\Time\Repetition\Ical\RruleFrequency */
    private $frequency;

    /** @var int [0,n] */
    private $interval;

    /** @var int [0,n] */
    private $count;

    /** @var \Dogma\Time\DateTime */
    private $since;

    /** @var \Dogma\Time\DateTime|null */
    private $until;

    /** @var \Dogma\Time\DayOfWeek */
    private $weekStart;

    /** @var int[] [1,12] */
    private $months;

    /** @var int[] [1,53] [-1,-53] */
    private $weeks;

    /** @var int[] [1,366] [-1,-366] */
    private $yearDays;

    /** @var int[] [1,31] [-1,-31] */
    private $monthDays;

    /** @var \Dogma\Time\DayOfWeek[] */
    private $weekDays;

    /** @var int[] [1,53] [-1,-53] */
    private $weekDayIndexes;

    /** @var int[] [0,23] */
    private $hours;

    /** @var int[] [0,59] */
    private $minutes;

    /** @var int[] [0,59] */
    private $seconds;

    /** @var int[] [0,n] */
    private $setPositions;

}
