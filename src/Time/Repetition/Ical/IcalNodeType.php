<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

// spell-check-ignore: RDATE EXDATE EXRULE TZID DTSTART DTEND BYDAY TZNAME CALSCALE FREEBUSY TRANSP TZOFFSETFROM
// spell-check-ignore: TZOFFSETTO TZURL DTSTAMP VEVENT VTODO VJOURNAL VFREEBUSY VTIMEZONE VALARM

namespace Dogma\Time\Repetition\Ical;

use Dogma\Enum\StringEnum;
use Dogma\Geolocation\Position;
use Dogma\Time\Date;
use Dogma\Time\DateTime;
use Dogma\Time\Span\DateTimeSpan;
use Dogma\Type;
use Dogma\Web\Url;

class IcalNodeType extends StringEnum
{

    // list of dates for repeated event [date-time / date / period]
    public const REPETITION_DATE = 'RDATE'; // RDATE:19970714T123000Z

    // repeated event rule [RECUR]
    public const REPETITION_RULE = 'RRULE'; // RRULE:FREQ=MONTHLY;COUNT=10;BYDAY=1FR

    // excluded dates for repeated event [date-time / date]
    public const EXCLUDE_DATE = 'EXDATE'; // EXDATE:19960402T010000Z,19960403T010000Z,19960404T010000Z

    // repeated event excluded days rule [RECUR]
    public const EXCLUDE_RULE = 'EXRULE'; // RRULE:FREQ=MONTHLY;COUNT=10;BYDAY=1FR

    // time zone name
    public const TIME_ZONE_ID = 'TZID'; // TZID:America/New_York

    // timezone abbreviation
    public const TIME_ZONE_NAME = 'TZNAME'; // TZNAME:UTC

    // event start (or repeated event start time) [date-time / date]
    public const START_TIME = 'DTSTART'; // 20180101150000

    // event length [DURATION]
    public const DURATION = 'DURATION'; // DURATION:PT1H0M0S

    // gps position of event
    public const GPS_POSITION = 'GEO'; // GEO:37.386013;-122.082932

    // not supported
    public const CALENDAR_SCALE = 'CALSCALE';
    public const METHOD = 'METHOD';
    public const PRODUCT_ID = 'PRODID';
    public const VERSION = 'VERSION';
    public const ATTACH = 'ATTACH';
    public const CATEGORIES = 'CATEGORIES';
    public const CLASS_NODE = 'CLASS';
    public const COMMENT = 'COMMENT';
    public const DESCRIPTION = 'DESCRIPTION';
    public const LOCATION = 'LOCATION';
    public const PERCENT_COMPLETE = 'PERCENT-COMPLETE';
    public const PRIORITY = 'PRIORITY';
    public const RESOURCES = 'RESOURCES';
    public const STATUS = 'STATUS';
    public const SUMMARY = 'SUMMARY';
    public const DUE = 'DUE';
    public const COMPLETED = 'COMPLETED';
    public const END_TIME = 'DTEND';
    public const FREE_BUSY_INTERVAL = 'FREEBUSY';
    public const TRANSPARENT = 'TRANSP';
    public const TIMEZONE_OFFSET_FROM = 'TZOFFSETFROM';
    public const TIMEZONE_OFFSET_TO = 'TZOFFSETTO';
    public const TIMEZONE_URL = 'TZURL';
    public const ATTENDEE = 'ATTENDEE';
    public const CONTACT = 'CONTACT';
    public const ORGANIZER = 'ORGANIZER';
    public const RECURRENCE_ID = 'RECURRENCE-ID';
    public const RELATED_TO = 'RELATED-TO';
    public const URL = 'URL';
    public const UID = 'UID';
    public const ACTION = 'ACTION';
    public const REPEAT = 'REPEAT';
    public const TRIGGER = 'TRIGGER';
    public const CREATED = 'CREATED';
    public const TIMESTAMP = 'DTSTAMP';
    public const LAST_MODIFIED = 'LAST-MODIFIED';
    public const SEQUENCE = 'SEQUENCE';
    public const REQUEST_STATUS = 'REQUEST-STATUS';

    // components ()
    public const CALENDAR = 'CALENDAR';
    public const EVENT = 'VEVENT';
    public const TODO = 'VTODO';
    public const JOURNAL = 'VJOURNAL';
    public const FREE_BUSY = 'VFREEBUSY';
    public const TIMEZONE = 'VTIMEZONE';
    public const ALARM = 'VALARM';

    public $types = [
        self::CALENDAR => IcalNode::class,
        // calendar components
        self::EVENT => IcalNode::class,
        self::TODO => IcalNode::class,
        self::JOURNAL => IcalNode::class,
        self::FREE_BUSY => IcalNode::class,
        self::TIMEZONE => IcalNode::class,
        self::ALARM => IcalNode::class,
        // calendar properties
        self::CALENDAR_SCALE => Type::STRING,
        self::METHOD => Type::STRING,
        self::PRODUCT_ID => Type::STRING,
        self::VERSION => Type::STRING,
        // descriptive properties
        self::ATTACH => Type::STRING,
        self::CATEGORIES => Type::STRING,
        self::CLASS_NODE => Type::STRING,
        self::COMMENT => Type::STRING,
        self::DESCRIPTION => Type::STRING,
        self::GPS_POSITION => Position::class,
        self::LOCATION => Type::STRING,
        self::PERCENT_COMPLETE => Type::INT,
        self::PRIORITY => Type::INT,
        self::RESOURCES => Type::STRING,
        self::STATUS => Type::STRING,
        self::SUMMARY => Type::STRING,
        // date/time properties
        self::COMPLETED => DateTime::class,
        self::END_TIME => [DateTime::class, Date::class],
        self::DUE => [DateTime::class, Date::class],
        self::START_TIME => [DateTime::class, Date::class],
        self::DURATION => DateTimeSpan::class,
        self::FREE_BUSY => [DateTime::class, Date::class],
        self::TRANSPARENT => Type::STRING,
        // timezone properties
        self::TIME_ZONE_ID => \DateTimeZone::class,
        self::TIME_ZONE_NAME => \DateTimeZone::class,
        self::TIMEZONE_OFFSET_FROM => Type::INT, ///
        self::TIMEZONE_OFFSET_TO => Type::INT, ///
        self::TIMEZONE_URL => Url::class,
        // relationship properties
        self::ATTENDEE => Type::STRING, /// address
        self::CONTACT => Type::STRING,
        self::ORGANIZER => Type::STRING, /// address
        self::RECURRENCE_ID => [DateTime::class, Date::class],
        self::RELATED_TO => Type::STRING,
        self::URL => Url::class,
        self::UID => Type::STRING,
        // recurrence properties
        self::EXCLUDE_DATE => [DateTime::class, Date::class],
        self::REPETITION_DATE => [DateTime::class, Date::class],
        self::EXCLUDE_RULE => Rrule::class,
        self::REPETITION_RULE => Rrule::class,
        // alarm properties
        self::ACTION => Type::STRING,
        self::REPEAT => Type::INT,
        self::TRIGGER => [DateTimeSpan::class, DateTime::class],
        // change management properties
        self::CREATED => DateTime::class,
        self::TIMESTAMP => DateTime::class,
        self::LAST_MODIFIED => DateTime::class,
        self::SEQUENCE => Type::INT,
        // misc properties
        self::REQUEST_STATUS => Type::STRING,
    ];

}
