<?php

namespace Dogma\Time\Mapping;

use Dogma\Mapping\Mapper;
use Dogma\Time\Date;
use Dogma\Time\DateTime;
use Dogma\Time\Time;
use Dogma\Type;

/**
 * Creates Date/Time/DateTime instances from raw data and vice versa
 */
class DateTimeHandler implements \Dogma\Mapping\Type\Handler
{
    use \Dogma\StrictBehaviorMixin;

    /** @var string */
    private $dateTimeFormat;

    /** @var string */
    private $dateFormat;

    /** @var string */
    private $timeFormat;

    /** @var \DateTimeZone|null */
    private $timeZone;

    public function __construct(
        string $dateTimeFormat = 'Y-m-d H:i:s',
        string $dateFormat = 'Y-m-d',
        string $timeFormat = 'H:i:s',
        \DateTimeZone $timeZone = null
    )
    {
        $this->dateTimeFormat = $dateTimeFormat;
        $this->dateFormat = $dateFormat;
        $this->timeFormat = $timeFormat;
        $this->timeZone = $timeZone;
    }

    public function acceptsType(Type $type): bool
    {
        return $type->isImplementing(DateTime::class)
            || $type->isImplementing(Date::class)
            || $type->isImplementing(Time::class);
    }

    /**
     * @param \Dogma\Type $type
     * @return null
     */
    public function getParameters(Type $type)
    {
        return null;
    }

    /**
     * @param \Dogma\Type $type
     * @param mixed $value
     * @param \Dogma\Mapping\Mapper $mapper
     * @return \Dogma\Time\DateTime|\Dogma\Time\Date|\Dogma\Time\Time
     */
    public function createInstance(Type $type, $value, Mapper $mapper)
    {
        return $type->getInstance($value);
    }

    /**
     * @param \Dogma\Type $type
     * @param \Dogma\Time\DateTime|\Dogma\Time\Date|\Dogma\Time\Time $instance
     * @param \Dogma\Mapping\Mapper $mapper
     * @return string
     */
    public function exportInstance(Type $type, $instance, Mapper $mapper): string
    {
        return $instance->format();
    }

}
