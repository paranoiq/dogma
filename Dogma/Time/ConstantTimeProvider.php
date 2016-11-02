<?php

namespace Dogma\Time;

class ConstantTimeProvider implements TimeProvider
{
    use \Dogma\StrictBehaviorMixin;

    /** @var \Dogma\Time\DateTime */
    private $dateTime;

    public function __construct(DateTime $dateTime = null)
    {
        if (!$dateTime) {
            $dateTime = new DateTime();
        }
        $this->dateTime = $dateTime;
    }

    public function getDateTime(): DateTime
    {
        return $this->dateTime;
    }

}
